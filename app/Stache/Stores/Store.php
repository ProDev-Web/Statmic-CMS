<?php

namespace Statamic\Stache\Stores;

use Statamic\API\File;
use Statamic\Stache\Indexes;
use Illuminate\Support\Facades\Cache;
use Facades\Statamic\Stache\Traverser;

abstract class Store
{
    protected $directory;
    protected $customIndexes = [];
    protected $defaultIndexes = ['id'];
    protected $storeIndexes = [];
    protected $usedIndexes;
    protected $fileChangesHandled = false;
    protected $paths;
    protected $fileItems;
    protected $shouldCacheFileItems = false;

    public function directory($directory = null)
    {
        if ($directory === null) {
            return $this->directory;
        }

        $this->directory = str_finish($directory, '/');

        return $this;
    }

    public function index($name)
    {
        $this->handleFileChanges();

        return $this->resolveIndex($name)->load();
    }

    protected function resolveIndex($name)
    {
        $cached = app('stache.indexes');

        if ($cached->has($key = "{$this->key()}.{$name}")) {
            return $cached->get($key);
        }

        $class = $this->indexes()->get($name, Indexes\Value::class);

        $index = new $class($this, $name);

        $cached->put($key, $index);

        return $index;
    }

    public function getItemsFromFiles()
    {
        if ($this->shouldCacheFileItems && $this->fileItems) {
            return $this->fileItems;
        }

        $files = Traverser::filter([$this, 'getItemFilter'])->traverse($this);

        $items = $files->map(function ($timestamp, $path) {
            return $this->getItemByPath($path);
        })->keyBy(function ($item) {
            return $this->getItemKey($item);
        });

        return $this->fileItems = $items;
    }

    public function getItemKey($item)
    {
        return $item->id();
    }

    public function getItems($keys)
    {
        $this->handleFileChanges();

        return collect($keys)->map(function ($key) {
            return $this->getItem($key);
        });
    }

    abstract public function getItem($key);

    public function indexUsage()
    {
        $key = $this->indexUsageCacheKey();

        return $this->usedIndexes = $this->usedIndexes ?? collect(Cache::get($key, []));
    }

    public function cacheIndexUsage($index)
    {
        $indexes = $this->indexUsage();

        if ($indexes->contains($index = $index->name())) {
            $this->usedIndexes = $indexes;
            return;
        }

        $indexes->push($index);

        $this->usedIndexes = $indexes;

        Cache::put($this->indexUsageCacheKey(), $indexes->all());
    }

    protected function indexUsageCacheKey()
    {
        return "stache::indexes::{$this->key()}::_indexes";
    }

    public function indexes($withUsages = true)
    {
        $storeIndexConfigKey = $this instanceof ChildStore ? $this->parent->key() : $this->key();

        $indexes = collect(array_merge(
            $this->defaultIndexes,
            $this->storeIndexes(),
            config('statamic.stache.indexes', []),
            config("statamic.stache.stores.{$storeIndexConfigKey}.indexes", [])
        ));

        if ($withUsages) {
            $indexes = $indexes->merge($this->indexUsage()->all());
        }

        return $indexes->unique(function ($value, $key) {
            return is_int($key) ? $value : $key;
        })->mapWithKeys(function ($index, $key) {
            return is_int($key)
                ? [$index => Indexes\Value::class]
                : [$key => $index];
        });
    }

    public function resolveIndexes($withUsages = true)
    {
        return $this->indexes($withUsages)->map(function ($index, $name) {
            return $this->resolveIndex($name);
        });
    }

    protected function storeIndexes()
    {
        return $this->storeIndexes;
    }

    public function handleFileChanges()
    {
        // We only want to act on any file changes one time per store.
        if ($this->fileChangesHandled) {
            return;
        }

        $this->fileChangesHandled = true;

        // This whole process can be disabled to save overhead, at the expense of needing to update
        // the cache manually. If the Control Panel is being used, or the cache is cleared when
        // deployed, for example, this will happen naturally and disabling is a good idea.
        if (! config('statamic.stache.watcher')) {
            return;
        }

        // Get the existing files and timestamps from the cache.
        $cacheKey = "stache::timestamps::{$this->key()}";
        $existing = collect(Cache::get($cacheKey, []));

        // Get the files and timestamps from the filesystem right now.
        $files = Traverser::filter([$this, 'getFileFilter'])->traverse($this);

        // Cache the files and timestamps, ready for comparisons on the next request.
        // We'll do it now since there are multiple early returns coming up.
        Cache::forever($cacheKey, $files->all());

        // If there are no existing file timestamps in the cache, there's nothing to update.
        if ($existing->isEmpty()) {
            return;
        }

        // Get all the modified files.
        // This includes both newly added files, and existing files that have been changed.
        $modified = $files->filter(function ($timestamp, $path) use ($existing) {
            // No existing timestamp, it must be a new file.
            if (! $existingTimestamp = $existing->get($path)) {
                return true;
            }

            // If the existing timestamp is less/older, it's modified.
            return $existingTimestamp < $timestamp;
        });

        // Get all the deleted files.
        // This would be any paths that exist in the cached array that aren't there anymore.
        $deleted = $existing->keys()->diff($files->keys())->values();

        // If there are no modified or deleted files, there's nothing to update.
        if ($modified->isEmpty() && $deleted->isEmpty()) {
            return;
        }

        // Get a path to key mapping, so we can easily get the keys of existing files.
        $pathMap = $this->paths()->flip();

        // Flush cached instances of deleted items.
        $deleted->each(function ($path) {
            if ($key = $this->getKeyFromPath($path)) {
                $this->forgetItem($key);
            }
        });

        // Flush the cached instances of modified items.
        $modified->each(function ($timestamp, $path) use ($pathMap) {
            if ($key = $pathMap->get($path)) {
                $this->forgetItem($key);
            }
        });

        // Load all the indexes so we're dealing with fresh items in both loops.
        $indexes = $this->resolveIndexes()->each->load();

        // Remove deleted items from every index.
        $indexes->each(function ($index) use ($deleted, $pathMap) {
            $deleted->each(function ($path) use ($index, $pathMap) {
                if ($key = $pathMap->get($path)) {
                    $index->forgetItem($key);
                }
            });
        });

        // Get items from every file that was modified.
        $modified = $modified->map(function ($timestamp, $path) use ($pathMap) {
            if ($key = $pathMap->get($path)) {
                return $this->getItem($key);
            }

            $item = $this->makeItemFromFile($path, File::get($path));

            $this->cacheItem($item);

            return $item;
        });

        // There may be duplicate items when we're dealing with items that are split across files.
        // For example, a global set can have the base file, plus a file for each localization.
        // They'd all resolve to the same item though, so just reduce them down to the same.
        $modified = $modified->unique(function ($item) {
            return $this->getItemKey($item);
        });

        $modified->each(function ($item) {
            $this->handleModifiedItem($item);
        });

        // Update modified items in every index.
        $indexes->each(function ($index) use ($modified, $pathMap) {
            $modified->each(function ($item) use ($index) {
                $index->updateItem($item);
            });
        });
    }

    protected function handleModifiedItem($item)
    {
        //
    }

    public function paths()
    {
        if ($this->paths) {
            return $this->paths;
        }

        if ($paths = Cache::get($this->pathsCacheKey())) {
            return $this->paths = collect($paths);
        }

        $files = Traverser::filter([$this, 'getItemFilter'])->traverse($this);

        $paths = $files->mapWithKeys(function ($timestamp, $path) {
            $item = $this->makeItemFromFile($path, File::get($path));
            $this->cacheItem($item);
            return [$this->getItemKey($item) => $path];
        });

        $this->cachePaths($paths);

        return $paths;
    }

    protected function forgetPath($key)
    {
        $paths = $this->paths();

        unset($paths[$key]);

        $this->cachePaths($paths);
    }

    protected function setPath($key, $path)
    {
        $paths = $this->paths();

        $paths[$key] = $path;

        $this->cachePaths($paths);
    }

    protected function cachePaths($paths)
    {
        Cache::forever($this->pathsCacheKey(), $paths->all());

        $this->paths = $paths;
    }

    protected function pathsCacheKey()
    {
        return "stache::indexes::{$this->key()}::_paths";
    }

    public function clear()
    {
        $this->paths()->keys()->each(function ($key) {
            $this->forgetItem($key);
        });

        $this->resolveIndexes()->each(function ($index) {
            $index->clear();
            app('stache.indexes')->forget("{$this->key()}.{$index->name()}");
        });

        Cache::forget($this->indexUsageCacheKey());

        $this->paths = null;
        Cache::forget($this->pathsCacheKey());
    }

    public function warm()
    {
        $this->shouldCacheFileItems = true;

        $this->resolveIndexes()->each->update();

        $this->shouldCacheFileItems = false;
        $this->fileItems = null;
    }
}

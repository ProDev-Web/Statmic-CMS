<?php

namespace Statamic\Stache\Stores;

use Statamic\Facades;
use Statamic\Support\Str;
use Statamic\Facades\File;
use Statamic\Facades\Site;
use Statamic\Facades\YAML;
use Statamic\Stache\Indexes;
use Symfony\Component\Finder\SplFileInfo;

class StructuresStore extends BasicStore
{
    protected $storeIndexes = [
        'uri' => Indexes\StructureUris::class,
    ];

    public function key()
    {
        return 'structures';
    }

    public function getItemFilter(SplFileInfo $file)
    {
        // The structures themselves should only exist in the root
        // (ie. no slashes in the filename)
        $filename = str_after($file->getPathName(), $this->directory);
        return substr_count($filename, '/') === 0 && $file->getExtension() === 'yaml';
    }

    public function makeItemFromFile($path, $contents)
    {
        $relative = str_after($path, $this->directory);
        $handle = str_before($relative, '.yaml');

        // If it's a tree file that was requested, instead assume that the
        // base file was requested. The tree will get made as part of it.
        if (Site::hasMultiple() && str_contains($relative, '/')) {
            [$site, $relative] = explode('/', $relative, 2);
            $handle = str_before($relative, '.yaml');
            $path = $this->directory . $handle . '.yaml';
            $data = YAML::file($path)->parse();
            return $this->makeMultiSiteStructureFromFile($handle, $path, $data);
        }

        $data = YAML::file($path)->parse($contents);

        return Site::hasMultiple()
            ? $this->makeMultiSiteStructureFromFile($handle, $path, $data)
            : $this->makeSingleSiteStructureFromFile($handle, $path, $data);
    }

    protected function makeSingleSiteStructureFromFile($handle, $path, $data)
    {
        $structure = $this
            ->makeBaseStructureFromFile($handle, $path, $data)
            ->sites([$site = Site::default()->handle()])
            ->maxDepth($data['max_depth'] ?? null)
            ->collections($data['collections'] ?? null);

        return $structure->addTree(
            $structure
                ->makeTree($site)
                ->root($data['root'] ?? null)
                ->tree($data['tree'] ?? [])
        );
    }

    protected function makeMultiSiteStructureFromFile($handle, $path, $data)
    {
        $structure = $this->makeBaseStructureFromFile($handle, $path, $data);

        $structure->sites()->map(function ($site) use ($structure) {
            return $this->makeTree($structure, $site);
        })->filter()->each(function ($variables) use ($structure) {
            $structure->addTree($variables);
        });

        return $structure;
    }

    protected function makeBaseStructureFromFile($handle, $path, $data)
    {
        return Facades\Structure::make()
            ->handle($handle)
            ->title($data['title'] ?? null)
            ->sites($data['sites'] ?? null)
            ->maxDepth($data['max_depth'] ?? null)
            ->collections($data['collections'] ?? null)
            ->expectsRoot($data['expects_root'] ?? false)
            ->initialPath($path);
    }

    protected function makeTree($structure, $site)
    {
        $tree = $structure->makeTree($site);

        // todo: cache the reading and parsing of the file
        if (! File::exists($path = $tree->path())) {
            return;
        }
        $data = YAML::file($path)->parse();

        return $tree
            ->initialPath($path)
            ->root($data['root'] ?? null)
            ->tree($data['tree'] ?? []);
    }

    public function getItemKey($item)
    {
        return $item->handle();
    }

    protected function getKeyFromPath($path)
    {
        if ($key = parent::getKeyFromPath($path)) {
            return $key;
        }

        return pathinfo($path, PATHINFO_FILENAME);
    }

    public function filter($file)
    {
        return $file->getExtension() === 'yaml';
    }
}

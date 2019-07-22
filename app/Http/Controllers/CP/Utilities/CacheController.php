<?php

namespace Statamic\Http\Controllers\CP\Utilities;

use Statamic\API\Str;
use League\Glide\Server;
use Statamic\API\Stache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\StaticCaching\Cacher as StaticCacher;

class CacheController extends CpController
{
    public function index()
    {
        return view('statamic::utilities.cache', [
            'stache' => $this->getStacheStats(),
            'cache' => $this->getApplicationCacheStats(),
            'static' => $this->getStaticCacheStats(),
            'images' => $this->getImageCacheStats()
        ]);
    }

    protected function getStacheStats()
    {
        $time = Stache::buildTime();
        $built = Stache::buildDate();

        return [
            'records' => Stache::fileCount(),
            'size' => Str::fileSizeForHumans(Stache::fileSize()),
            'time' => $time ? Str::timeForHumans($time) : 'Refresh',
            'rebuilt' => $built ? $built->diffForHumans() : 'Refresh',
        ];
    }

    protected function getApplicationCacheStats()
    {
        $driver = config('cache.default');
        $driver = ($driver === 'statamic') ? 'file (statamic)' : $driver;
        return compact('driver');
    }

    protected function getImageCacheStats()
    {
        $files = collect(app(Server::class)->getCache()->listContents('', true))
            ->filter(function ($file) {
                return $file['type'] === 'file';
            });

        return [
            'count' => $files->count(),
            'size' => Str::fileSizeForHumans($files->reduce(function ($size, $file) {
                return $size + $file['size'];
            }, 0)),
        ];
    }

    protected function getStaticCacheStats()
    {
        $strategy = config('statamic.static_caching.strategy');

        return [
            'enabled' => (bool) $strategy,
            'strategy' => $strategy ?? 'Disabled',
            'count' => app(StaticCacher::class)->getUrls()->count(),
        ];
    }

    public function clear(Request $request, $cache)
    {
        $method = 'clear' . ucfirst($cache) . 'Cache';

        return $this->$method();
    }

    protected function clearAllCache()
    {
        $this->clearStacheCache();
        $this->clearStaticCache();
        $this->clearApplicationCache();
        $this->clearImageCache();

        return back()->withSuccess('All caches cleared.');
    }

    protected function clearStacheCache()
    {
        Stache::clear();

        return back()->withSuccess('Stache cleared.');
    }

    protected function clearStaticCache()
    {
        app(StaticCacher::class)->flush();

        return back()->withSuccess('Static page cache cleared.');
    }

    protected function clearApplicationCache()
    {
        Artisan::call('cache:clear');

        // TODO: Stache doesn't appear to be clearing?
        // Maybe related to https://github.com/statamic/three-cms/issues/149

        return back()->withSuccess('Application cache cleared.');
    }

    protected function clearImageCache()
    {
        Artisan::call('statamic:glide:clear');

        return back()->withSuccess('Image cache cleared.');
    }
}

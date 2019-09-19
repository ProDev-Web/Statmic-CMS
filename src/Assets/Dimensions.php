<?php

namespace Statamic\Assets;

use League\Flysystem\MountManager;
use Statamic\Imaging\ImageGenerator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class Dimensions
{
    const CACHE_EXPIRY_MINUTES = 60;

    /**
     * @var Asset
     */
    private $asset;

    /**
     * @param $generator ImageGenerator
     */
    public function __construct(ImageGenerator $generator)
    {
        $this->generator = $generator;
    }

    public function asset(Asset $asset)
    {
        $this->asset = $asset;

        return $this;
    }

    /**
     * Get the dimensions of an asset, and cache them.
     *
     * @return array
     */
    public function get()
    {
        if (! $this->asset->isImage()) {
            return [null, null];
        }

        if ($cached = $this->cached()) {
            return $cached;
        }

        $this->cache($dimensions = $this->getImageDimensions());

        return $dimensions;
    }

    /**
     * Get the width of the asset
     *
     * @return int
     */
    public function width()
    {
        return array_get($this->get(), 0);
    }

    /**
     * Get the height of the asset
     *
     * @return int
     */
    public function height()
    {
        return array_get($this->get(), 1);
    }

    /**
     * Get the dimensions
     *
     * @return array
     */
    private function getImageDimensions()
    {
        // Since assets may be located on external platforms like Amazon S3, we can't simply
        // grab the dimensions. So we'll copy it locally and read the dimensions from there.
        $manager = new MountManager([
            'source' => $this->asset->disk()->filesystem()->getDriver(),
            'cache' => $cache = $this->getCacheFlysystem()
        ]);

        $cachePath = "{$this->asset->containerId()}/{$this->asset->path()}";

        $manager->copy("source://{$this->asset->path()}", "cache://{$cachePath}");

        $size = getimagesize($cache->getAdapter()->getPathPrefix() . $cachePath);

        $cache->delete($cachePath);

        return array_splice($size, 0, 2);
    }

    /**
     * Get the cached dimension value
     *
     * @return array|null
     */
    private function cached()
    {
        return Cache::get($this->cacheKey());
    }

    /**
     * Cache the dimensions
     *
     * @param array $dimensions
     * @return void
     */
    private function cache($dimensions)
    {
        Cache::put($this->cacheKey(), $dimensions, now()->addMinutes(self::CACHE_EXPIRY_MINUTES));
    }

    private function cacheKey()
    {
        return 'assets.dimensions.' . $this->asset->containerId() . '.' . $this->asset->path();
    }

    private function getCacheFlysystem()
    {
        $disk = 'dimensions-cache';

        config(["filesystems.disks.{$disk}" => [
            'driver' => 'local',
            'root' => storage_path('statamic/dimensions-cache')
        ]]);

        return Storage::disk($disk)->getDriver();
    }
}

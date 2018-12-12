<?php

namespace Statamic\Imaging;

use Statamic\API\Image;
use Statamic\API\Config;
use League\Glide\ServerFactory;
use Statamic\Imaging\ResponseFactory as LaravelResponseFactory;

class GlideServer
{
    /**
     * Create glide server.
     *
     * @return \League\Glide\Server
     */
    public function create()
    {
       return ServerFactory::create([
            'source'   => base_path(), // this gets overriden on the fly by the image generator
            'cache'    => $this->cachePath(),
            'base_url' => Config::get('statamic.assets.image_manipulation.route', 'img'),
            'response' => new LaravelResponseFactory(app('request')),
            'driver'   => Config::get('statamic.assets.image_manipulation.driver'),
            'cache_with_file_extensions' => true,
            'presets' => $this->presets(),
        ]);
    }

    /**
     * Get glide cache path.
     *
     * @return string
     */
    public function cachePath()
    {
        return Config::get('statamic.assets.image_manipulation.cache')
            ? Config::get('statamic.assets.image_manipulation.cache_path')
            : storage_path('glide');
    }

    /**
     * Get glide presets.
     *
     * @return array
     */
    private function presets()
    {
        $presets = Config::getImageManipulationPresets();

        if (config('statamic.cp.enabled')) {
            $presets = array_merge($presets, Image::getCpImageManipulationPresets());
        }
    }
}

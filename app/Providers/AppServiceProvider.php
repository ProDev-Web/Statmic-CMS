<?php

namespace Statamic\Providers;

use Statamic\DataStore;
use Statamic\Extensions\FileStore;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    private $root = __DIR__.'/../..';

    public function boot()
    {
        // We have our own extension of Laravel's file-based cache driver.
        Cache::extend('statamic', function () {
            return Cache::repository(new FileStore(
                $this->app['files'],
                $this->app['config']["cache.stores.file"]['path']
            ));
        });

        $this->app[\Illuminate\Contracts\Http\Kernel::class]
             ->pushMiddleware(\Statamic\Http\Middleware\PersistStache::class);

        $this->app->booted(function () {
            $this->loadRoutesFrom("{$this->root}/routes/routes.php");
        });

        $this->loadViewsFrom("{$this->root}/resources/views", 'statamic');

        $this->publishes([
            "{$this->root}/resources/dist" => public_path('resources/cp')
        ], 'statamic');
    }

    public function register()
    {
        $this->app->singleton('Statamic\DataStore', function() {
            return new DataStore;
        });
    }
}

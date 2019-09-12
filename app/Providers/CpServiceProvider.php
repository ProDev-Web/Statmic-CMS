<?php

namespace Statamic\Providers;

use Statamic\Facades\User;
use Statamic\Facades\Site;
use Statamic\Statamic;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Statamic\Extensions\Translation\Loader;
use Statamic\Extensions\Translation\Translator;
use Facades\Statamic\Fields\FieldtypeRepository;

class CpServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->preventRegistration()) {
            return;
        }

        View::composer('statamic::*', function ($view) {
            $view->with('user', User::current());
        });

        tap($this->app->make('view'), function ($view) {
            $view->composer('statamic::layout', 'Statamic\Http\ViewComposers\PermissionComposer');
        });

        Statamic::provideToScript([
            'translationLocale' => $this->app['translator']->locale(),
            'translations' => $this->app['translator']->toJson(),
            'sites' => $this->sites(),
            'selectedSite' => Site::selected()->handle(),
            'ampEnabled' => config('statamic.amp.enabled'),
            'preloadableFieldtypes' => FieldtypeRepository::preloadable()->keys(),
            'livePreview' => config('statamic.live_preview'),
            'locale' => config('app.locale'),
        ]);
    }

    protected function sites()
    {
        return Site::all()->map(function ($site) {
            return [
                'name' => $site->name(),
                'handle' => $site->handle(),
            ];
        })->values();
    }

    public function register()
    {
        if ($this->preventRegistration()) {
            return;
        }

        $this->app->extend('translation.loader', function ($loader, $app) {
            return new Loader($loader, $app['path.lang']);
        });

        $this->app->extend('translator', function ($translator, $app) {
            return new Translator($app['files'], $translator->getLoader(), $translator->getLocale());
        });
    }

    private function preventRegistration()
    {
        if (app()->environment('testing')) {
            return false;
        }

        return ! Statamic::isCpRoute();
    }
}

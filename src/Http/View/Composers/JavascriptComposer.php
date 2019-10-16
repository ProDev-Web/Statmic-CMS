<?php

namespace Statamic\Http\View\Composers;

use Facades\Statamic\Fields\FieldtypeRepository;
use Illuminate\View\View;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Statamic\Statamic;

class JavascriptComposer
{
    const VIEWS = ['statamic::layout'];

    public function compose(View $view)
    {
        Statamic::provideToScript([
            'version' => Statamic::version(),
            'laravelVersion' => app()->version(),
            'csrfToken' => csrf_token(),
            'cpRoot' => str_start(config('statamic.cp.route'), '/'),
            'urlPath' => '/' . request()->path(),
            'resourceUrl' => Statamic::assetUrl(),
            'locales' => config('statamic.system.locales'),
            'conditions' => [],
            'MediumEditorExtensions' => [],
            'flash' => Statamic::flash(),
            'ajaxTimeout' => config('statamic.system.ajax_timeout'),
            'googleDocsViewer' => config('statamic.assets.google_docs_viewer'),
            'user' => ($user = User::current()) ? $user->toJavascriptArray() : [],
            'paginationSize' => config('statamic.cp.pagination_size'),
            'translationLocale' => app('translator')->locale(),
            'translations' => app('translator')->toJson(),
            'sites' => $this->sites(),
            'selectedSite' => Site::selected()->handle(),
            'ampEnabled' => config('statamic.amp.enabled'),
            'preloadableFieldtypes' => FieldtypeRepository::preloadable()->keys(),
            'livePreview' => config('statamic.live_preview'),
            'locale' => config('app.locale'),
            'permissions' => base64_encode(json_encode(User::current()->permissions()))
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
}

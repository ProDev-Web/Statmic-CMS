<?php

namespace Statamic\Http\Controllers;

use Statamic\Facades\URL;
use Statamic\Facades\Data;
use Statamic\Facades\Site;
use Statamic\Statamic;
use Statamic\Facades\Content;
use Illuminate\Http\Request;
use Statamic\Exceptions\NotFoundHttpException;

/**
 * The front-end controller
 */
class FrontendController extends Controller
{
    /**
     * Handles all URLs
     *
     * @return string
     */
    public function index(Request $request)
    {
        $url = Site::current()->relativePath(
            str_finish($request->getUri(), '/')
        );

        if ($url === '') {
            $url = '/';
        }

        if (Statamic::isAmpRequest()) {
            $url = str_after($url, '/' . config('statamic.amp.route'));
        }

        $url = $this->removeIgnoredSegments($url);

        if (str_contains($url, '?')) {
            $url = substr($url, 0, strpos($url, '?'));
        }

        if ($data = Data::findByUri($url, Site::current()->handle())) {
            debugbar()->addMessage("Item {$data->id()} loaded by URL Request", 'statamic');
            return $data;
        }

        throw new NotFoundHttpException;
    }

    public function removeIgnoredSegments($uri)
    {
        $ignore = config('statamic.routes.ignore', []);

        return collect(explode('/', $uri))->reject(function ($segment) use ($ignore) {
            return in_array($segment, $ignore);
        })->implode('/');
    }
}

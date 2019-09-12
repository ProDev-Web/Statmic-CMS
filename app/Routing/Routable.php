<?php

namespace Statamic\Routing;

use Statamic\Facades\Str;
use Statamic\Facades\URL;
use Statamic\Contracts\Routing\UrlBuilder;

trait Routable
{
    protected $slug;

    abstract public function route();
    abstract public function routeData();

    public function slug($slug = null)
    {
        return $this->fluentlyGetOrSet('slug')->setter(function ($slug) {
            return Str::slug($slug);
        })->args(func_get_args());
    }

    public function uri()
    {
        if (! $route = $this->route()) {
            return null;
        }

        return app(UrlBuilder::class)->content($this)->build($route);
    }

    public function url()
    {
        return URL::makeRelative($this->absoluteUrl());
    }

    public function absoluteUrl()
    {
        return vsprintf('%s/%s', [
            rtrim($this->site()->absoluteUrl(), '/'),
            ltrim($this->uri(), '/')
        ]);
    }

    public function ampUrl()
    {
        return !$this->ampable() ? null : vsprintf('%s/%s/%s', [
            rtrim($this->site()->absoluteUrl(), '/'),
            config('statamic.amp.route'),
            ltrim($this->uri(), '/')
        ]);
    }
}

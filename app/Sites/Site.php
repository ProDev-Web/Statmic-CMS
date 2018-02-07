<?php

namespace Statamic\Sites;

use Statamic\API\Str;


class Site
{
    protected $handle;
    protected $config;

    public function __construct($handle, $config)
    {
        $this->handle = $handle;
        $this->config = $config;
    }

    public function handle()
    {
        return $this->handle;
    }

    public function name()
    {
        return $this->config['name'];
    }

    public function locale()
    {
        return $this->config['locale'];
    }

    public function shortLocale()
    {
        return explode('-', str_replace('_', '-', $this->locale()))[0];
    }

    public function url()
    {
        return $this->config['url'];
    }

    public function relativePath($url)
    {
        $path = Str::removeLeft($url, $this->url());

        return Str::ensureLeft($path, '/');
    }
}
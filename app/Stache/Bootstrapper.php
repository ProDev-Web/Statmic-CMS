<?php

namespace Statamic\Stache;

use Statamic\Stache\Exceptions\EmptyStacheException;

class Bootstrapper
{
    const CONFIG_UPDATE_EVERY_REQUEST_KEY = 'statamic.stache.update_every_request';
    const CONFIG_GLIDE_ROUTE_KEY = 'statamic.assets.image_manipulation.route';

    public function shouldUpdate()
    {
        if ($this->updateEveryRequestIsDisabled()) {
            return false;
        }

        if ($this->isGlideRoute()) {
            return false;
        }

        return true;
    }

    protected function updateEveryRequestIsDisabled()
    {
        return ! config(self::CONFIG_UPDATE_EVERY_REQUEST_KEY);
    }

    protected function isGlideRoute()
    {
        $route = config(self::CONFIG_GLIDE_ROUTE_KEY);
        $route = str_finish($route, '/');
        $route = ltrim($route, '/');

        return starts_with(request()->path(), $route);
    }

    public function boot($stache)
    {
        $update = $this->shouldUpdate();

        try {
            $stache->load();
        } catch (EmptyStacheException $e) {
            $stache->startTimer();
            $update = true;
        }

        if ($update) {
            $stache->update();
        }

        $stache->heat();
    }
}

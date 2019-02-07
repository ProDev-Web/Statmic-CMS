<?php

namespace Statamic\Tags;

use Statamic\Tags\Tags;
use Statamic\View\Store;

class Yields extends Tags
{
    public function __call($method, $args)
    {
        $name = explode(':', $this->tag)[1];

        if ($yield = app(Store::class)->sections()->get($name)) {
            return $yield;
        }

        return $this->isPair ? $this->parse() : null;
    }
}

<?php

namespace Statamic\Facades;

use Statamic\Imaging\Manager;
use Illuminate\Support\Facades\Facade;

class Image extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Manager::class;
    }
}

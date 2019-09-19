<?php

namespace Statamic\Facades;

use Statamic\Extend\AddonRepository;
use Illuminate\Support\Facades\Facade;

class Addon extends Facade
{
    protected static function getFacadeAccessor()
    {
        return AddonRepository::class;
    }
}

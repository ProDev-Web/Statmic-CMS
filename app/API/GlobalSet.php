<?php

namespace Statamic\API;

use Illuminate\Support\Facades\Facade;
use Statamic\Contracts\Data\Repositories\GlobalRepository;

class GlobalSet extends Facade
{
    protected static function getFacadeAccessor()
    {
        return GlobalRepository::class;
    }
}

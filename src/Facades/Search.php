<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;

class Search extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Statamic\Search\Search::class;
    }
}

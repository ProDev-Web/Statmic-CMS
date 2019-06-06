<?php

namespace Statamic\API;

use Illuminate\Support\Facades\Facade;
use Statamic\Stache\Repositories\TaxonomyRepository;

class Taxonomy extends Facade
{
    protected static function getFacadeAccessor()
    {
        return TaxonomyRepository::class;
    }
}

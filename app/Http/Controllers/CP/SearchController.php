<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\API\Search;
use Statamic\API\Content;
use Illuminate\Http\Request;
use Statamic\Search\IndexNotFoundException;

class SearchController extends CpController
{
    public function __invoke(Request $request)
    {
        return Search::index()
            ->ensureExists()
            ->search($request->query('q'))
            ->get();
    }
}

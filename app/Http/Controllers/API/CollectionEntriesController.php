<?php

namespace Statamic\Http\Controllers\API;

use Statamic\API\Entry;
use Illuminate\Http\Request;
use Statamic\Http\Resources\EntryResource;
use Statamic\Http\Controllers\CP\CpController;

class CollectionEntriesController extends CpController
{
    use TemporaryResourcePagination;

    public function index($collection, Request $request)
    {
        $entries = static::paginate(Entry::whereCollection($collection));

        return EntryResource::collection($entries);
    }
}

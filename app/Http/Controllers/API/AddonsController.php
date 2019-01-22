<?php

namespace Statamic\Http\Controllers\API;

use Facades\Statamic\Extend\Marketplace;
use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;

class AddonsController extends CpController
{
    public function index(Request $request)
    {
        return Marketplace::query()
            ->filter($request->filter)
            ->search($request->q)
            ->paginate(30);
    }
}

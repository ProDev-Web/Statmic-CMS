<?php

namespace Statamic\Http\Controllers\API;

use Illuminate\Http\Request;
use Statamic\API\User;
use Statamic\Http\Resources\UserResource;
use Statamic\Http\Controllers\CP\CpController;

class UsersController extends CpController
{
    use TemporaryResourcePagination;

    public function index(Request $request)
    {
        $users = static::paginate(User::all()->values());

        return UserResource::collection($users);
    }
}

<?php

namespace Statamic\Addons\Can;

use Statamic\Facades\User;
use Statamic\Extend\API;

class CanAPI extends API
{
    public function can($permissions)
    {
        // Not logged in? This is the end of the road.
        if (! $user = me()) {
            return false;
        }

        $permissions = explode('|', $permissions);

        foreach ($permissions as $permission) {
            if ($user->can($permission)) {
                return true;
            }
        }

        return false;
    }
}

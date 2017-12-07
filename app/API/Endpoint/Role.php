<?php

namespace Statamic\API\Endpoint;

use Statamic\Config\Roles;

class Role
{
    /**
     * Retrieve the roles from storage
     *
     * @return \Illuminate\Support\Collection
     */
    private function roles()
    {
        return collect(app(Roles::class)->all());
    }

    /**
     * Get all the roles
     *
     * @return \Illuminate\Support\Collection
     */
    public function all()
    {
        return self::roles()->sortBy('title');
    }

    /**
     * Get a role
     *
     * @param string $id
     * @return \Statamic\Contracts\Permissions\Role
     */
    public function find($id)
    {
        return self::roles()->get($id);
    }

    /**
     * Get check if a role exists
     *
     * @param string $id
     * @return bool
     */
    public function exists($id)
    {
        return self::roles()->has($id);
    }

    /**
     * Get a role by handle
     *
     * @param  string $handle
     * @return \Statamic\Contracts\Permissions\Role
     */
    public function whereHandle($handle)
    {
        return self::roles()->filter(function ($role) use ($handle) {
            return $role->slug() === $handle;
        })->first();
    }
}

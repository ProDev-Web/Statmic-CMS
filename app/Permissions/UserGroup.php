<?php

namespace Statamic\Permissions;

use Statamic\API\User;
use Statamic\API\Role as RoleAPI;
use Illuminate\Support\Collection;
use Statamic\Contracts\Permissions\Role;
use Statamic\Contracts\Permissions\Permissible;
use Statamic\Contracts\Permissions\UserGroup as UserGroupContract;

class UserGroup implements UserGroupContract
{
    protected $title;
    protected $handle;
    protected $originalHandle;
    protected $users;
    protected $roles;

    public function __construct()
    {
        $this->users = collect();
        $this->roles = collect();
    }

    public function title(string $title = null)
    {
        if (is_null($title)) {
            return $this->title;
        }

        $this->title = $title;

        return $this;
    }

    public function handle(string $handle = null)
    {
        if (is_null($handle)) {
            return $this->handle;
        }

        if (! $this->originalHandle) {
            $this->originalHandle = $this->handle;
        }

        $this->handle = $handle;

        return $this;
    }

    public function originalHandle()
    {
        return $this->originalHandle;
    }

    public function users(): Collection
    {
        return $this->users;
    }

    public function addUser($user)
    {
        if (is_string($user)) {
            $user = User::find($user);
        }

        $this->users->put($user->id(), $user);

        return $this;
    }

    public function removeUser($user)
    {
        if ($user instanceof Permissible) {
            $user = $user->id();
        }

        $this->users->forget($user);

        return $this;
    }

    public function hasUser($user): bool
    {
        if ($user instanceof Permissible) {
            $user = $user->id();
        }

        return $this->users->has($user);
    }

    public function roles($roles = null)
    {
        return $this->roles;
    }

    public function assignRole($role)
    {
        if (is_string($role)) {
            $role = RoleAPI::find($role);
        }

        $this->roles->put($role->handle(), $role);

        return $this;
    }

    public function removeRole($role)
    {
        if ($role instanceof Role) {
            $role = $role->handle();
        }

        $this->roles->forget($role);

        return $this;
    }

    public function hasRole($role): bool
    {
        if ($role instanceof Role) {
            $role = $role->handle();
        }

        return $this->roles->has($role);
    }

    public function hasPermission($permission)
    {
        return $this->roles->reduce(function ($carry, $role) {
            return $carry->merge($role->permissions());
        }, collect())->contains($permission);
    }

    public function isSuper(): bool
    {
        return $this->hasPermission('super');
    }

    public function save()
    {
    }

    public function delete()
    {
    }
}

<?php

namespace Statamic\Contracts\Permissions;

use Illuminate\Support\Collection;

interface UserGroup
{
    public function title(string $title = null);
    public function handle(string $slug = null);
    public function users(): Collection;
    public function addUser($user);
    public function removeUser($user);
    public function hasUser($user): bool;
    public function roles($roles = null);
    public function hasRole($role): bool;
    public function assignRole($role);
    public function removeRole($role);
    public function hasPermission($permission);
    public function isSuper(): bool;
    public function save();
    public function delete();
}

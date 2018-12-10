<?php

namespace Tests;

use Statamic\API\Role;
use Illuminate\Support\Collection;
use Statamic\Auth\RoleRepository;
use Statamic\Contracts\Auth\RoleRepository as RepositoryContract;

trait FakesRoles
{
    private function setTestRoles($roles)
    {
        $roles = collect($roles)->map(function ($permissions, $handle) {
            return Role::make()
                ->handle($handle)
                ->addPermission($permissions);
        });

        $fake = new class($roles) extends RoleRepository {
            protected $roles;
            public function __construct($roles) {
                $this->roles = $roles;
            }
            public function all(): Collection {
                return $this->roles;
            }
        };

        app()->instance(RepositoryContract::class, $fake);
        Role::swap($fake);
    }
}

<?php

namespace Statamic\Contracts\Auth;

use Statamic\Contracts\Auth\User;
use Statamic\Auth\UserCollection;

interface UserRepository
{
    public function make(): User;
    public function all(): UserCollection;
    public function find($id): ?User;

    public function findByEmail(string $email): ?User;
    public function findByOAuthId(string $provider, string $id): ?User;
    public function current(): ?User;

    public function save(User $user);
    public function delete(User $user);
}

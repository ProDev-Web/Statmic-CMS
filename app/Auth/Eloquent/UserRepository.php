<?php

namespace Statamic\Auth\Eloquent;

use Statamic\Auth\UserCollection;
use Statamic\Contracts\Auth\User as UserContract;
use Statamic\Auth\UserRepository as BaseRepository;

class UserRepository extends BaseRepository
{
    protected $config;
    protected $roleRepository = RoleRepository::class;
    protected $userGroupRepository = UserGroupRepository::class;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function make(): UserContract
    {
        return (new User)->model(new Model);
    }

    public function all(): UserCollection
    {
        $users = $this->model('all')->keyBy('id')->map(function ($model) {
            return $this->makeUser($model);
        });

        return collect_users($users);
    }

    public function find($id): ?UserContract
    {
        if ($model = $this->model('find', $id)) {
            return $this->makeUser($model);
        }
    }

    public function findByEmail(string $email): ?UserContract
    {
        if (! $model = $this->model('where', 'email', $email)->first()) {
            return null;
        }

        return $this->makeUser($model);
    }

    public function model($method, ...$args)
    {
        $model = $this->config['model'];

        return call_user_func_array([$model, $method], $args);
    }

    /**
     * Convert an Eloquent User model to a Statamic User instance.
     *
     * @param  Model $model
     * @return User
     */
    private function makeUser(Model $model)
    {
        return User::fromModel($model);
    }

    public function query()
    {
        return new UserQueryBuilder($this->model('query'));
    }
}

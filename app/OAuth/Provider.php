<?php

namespace Statamic\OAuth;

use Closure;
use Statamic\API\Str;
use Statamic\API\File;
use Statamic\API\User;
use Statamic\Contracts\Auth\User as StatamicUser;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class Provider
{
    protected $name;
    protected $userCallback;
    protected $userDataCallback;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Get a Statamic user ID from an OAuth user ID
     *
     * @param string $id  User ID from an OAuth provider
     * @return string|null  A Statamic user ID
     */
    public function getUserId(string $id): ?string
    {
        return array_flip($this->getIds())[$id] ?? null;
    }

    public function findOrCreateUser($socialite): StatamicUser
    {
        if ($user = User::findByOAuthId($this->name, $socialite->getId())) {
            return $user;
        }

        return $this->createUser($socialite);
    }

    /**
     * Create a Statamic user from a Socialite user
     *
     * @param SocialiteUser $socialite
     * @return StatamicUser
     */
    public function createUser($socialite): StatamicUser
    {
        $user = $this->makeUser($socialite);

        $user->save();

        $this->setUserProviderId($user, $socialite->getId());

        return $user;
    }

    public function makeUser($socialite): StatamicUser
    {
        if ($this->userCallback) {
            return call_user_func($this->userCallback, $socialite);
        }

        return User::make()
            ->email($socialite->getEmail())
            ->data($this->userData($socialite));
    }

    public function userData($socialite)
    {
        if ($this->userDataCallback) {
            return call_user_func($this->userDataCallback, $socialite);
        }

        return ['name' => $socialite->getName()];
    }

    public function withUserData(Closure $callback)
    {
        $this->userDataCallback = $callback;
    }

    public function withUser(Closure $callback)
    {
        $this->userCallback = $callback;
    }

    public function loginUrl()
    {
        return route('statamic.oauth.login', $this->name);
    }

    public function label()
    {
        return Str::title($this->name);
    }

    protected function getIds()
    {
        if (! File::exists($path = $this->storagePath())) {
            $this->setIds([]);
        }

        return require $path;
    }

    protected function setIds($ids)
    {
        $contents = '<?php return ' . var_export($ids, true) . ';';

        File::put($this->storagePath(), $contents);
    }

    protected function setUserProviderId($user, $id)
    {
        $ids = $this->getIds();

        $ids[$user->id()] = $id;

        $this->setIds($ids);
    }

    protected function storagePath()
    {
        return storage_path("statamic/oauth/{$this->name}.php");
    }
}
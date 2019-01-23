<?php

namespace Statamic\API\Endpoint;

use Statamic\Contracts\Data\Globals\GlobalFactory;
use Statamic\Contracts\Data\Repositories\GlobalRepository;
use Statamic\Contracts\Data\Globals\GlobalSet as GlobalContract;

class GlobalSet
{
    public function make()
    {
        return $this->repo()->make();
    }

    /**
     * Create a global set
     *
     * @param string $slug
     * @return GlobalFactory
     */
    public function create($slug)
    {
        return app(GlobalFactory::class)->create($slug);
    }

    /**
     * Find a global set by handle
     *
     * @param string $handle
     * @return \Statamic\Contracts\Data\Globals\GlobalSet
     */
    public function whereHandle($handle)
    {
        return $this->repo()->handle($handle);
    }

    /**
     * Get global by ID
     *
     * @param string $id
     * @return \Statamic\Contracts\Data\Globals\GlobalSet
     */
    public function find($id)
    {
        return $this->repo()->find($id);
    }

    /**
     * Get all globals
     *
     * @return \Statamic\Data\Globals\GlobalCollection
     */
    public function all()
    {
        return $this->repo()->all()->sortBy(function ($global) {
            return $global->title();
        });
    }

    public function save($global)
    {
        $this->repo()->save($global);
    }

    protected function repo()
    {
        return app(GlobalRepository::class);
    }
}

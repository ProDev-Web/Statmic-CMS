<?php

namespace Statamic\API\Endpoint;

use Statamic\Contracts\Data\Repositories\CollectionRepository;
use Statamic\Contracts\Data\Entries\Collection as CollectionContract;

class Collection
{
    /**
     * Get all collections
     *
     * @return \Illuminate\Support\Collection
     */
    public function all()
    {
        return $this->repo()->all()->sortBy(function ($collection) {
            return $collection->title();
        });
    }

    /**
     * Get the handles of all collections
     *
     * @return array
     */
    public function handles()
    {
        return self::all()->keys()->all();
    }

    /**
     * Get a collection by handle
     *
     * @param string $handle
     * @return \Statamic\Contracts\Data\Entries\Collection
     */
    public function whereHandle($handle)
    {
        return $this->repo()->findByHandle($handle);
    }

    /**
     * Check if a collection exists by its handle
     *
     * @param string $handle
     * @return bool
     */
    public function handleExists($handle)
    {
        return self::whereHandle($handle) !== null;
    }

    /**
     * Create a collection
     *
     * @param string $handle
     * @return \Statamic\Contracts\Data\Entries\Collection
     */
    public function create($handle)
    {
        /** @var \Statamic\Contracts\Data\Entries\Collection $collection */
        $collection = app('Statamic\Contracts\Data\Entries\Collection');

        $collection->path($handle);

        return $collection;
    }

    public function save(CollectionContract $collection)
    {
        $this->repo()->save($collection);
    }

    protected function repo(): CollectionRepository
    {
        return app(CollectionRepository::class);
    }
}

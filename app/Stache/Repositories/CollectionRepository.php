<?php

namespace Statamic\Stache\Repositories;

use Statamic\Stache\Stache;
use Statamic\Data\Entries\Collection;
use Illuminate\Support\Collection as IlluminateCollection;
use Statamic\Contracts\Data\Repositories\CollectionRepository as RepositoryContract;

class CollectionRepository implements RepositoryContract
{
    protected $store;

    public function __construct(Stache $stache)
    {
        $this->store = $stache->store('collections');
    }

    public function all(): IlluminateCollection
    {
        $keys = $this->store->paths()->keys();

        return $this->store->getItems($keys);
    }

    public function findByHandle($handle): ?Collection
    {
        return $this->store->getItem($handle);
    }

    public function findByMount($mount): ?Collection
    {
        return $this->all()->first(function ($collection) use ($mount) {
            return optional($collection->mount())->id() === $mount->id();
        });
    }

    public function save(Collection $collection)
    {
        $this->store->save($collection);
    }

    public function delete(Collection $collection)
    {
        $this->store->removeItem($collection->handle(), $collection);

        $this->store->delete($collection);
    }

    public function updateEntryUris(Collection $collection)
    {
        $this->store->updateEntryUris($collection);
    }
}

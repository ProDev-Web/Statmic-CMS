<?php

namespace Statamic\Stache\Repositories;

use Statamic\Stache\Stache;
use Illuminate\Support\Collection;
use Statamic\API\Entry as EntryAPI;
use Statamic\Contracts\Data\Entries\Entry;
use Statamic\Contracts\Data\Structures\Structure;
use Statamic\Contracts\Data\Repositories\StructureRepository as RepositoryContract;

class StructureRepository implements RepositoryContract
{
    protected $stache;
    protected $store;

    public function __construct(Stache $stache)
    {
        $this->stache = $stache;
        $this->store = $stache->store('structures');
    }

    public function all(): Collection
    {
        $keys = $this->store->index('path')->keys();

        return $this->store->getItems($keys);
    }

    public function find($id): ?Structure
    {
        return $this->findByHandle($id);
    }

    public function findByHandle($handle): ?Structure
    {
        return $this->store->getItem($handle);
    }

    public function findEntryByUri(string $uri, string $site = null): ?Entry
    {
        $uri = str_start($uri, '/');

        $site = $site ?? $this->stache->sites()->first();

        if (! $key = $this->store->index('uri')->get($site.'::'.$uri)) {
            return null;
        }

        [$handle, $id] = explode('::', $key);

        return $this->find($handle)->in($site)->page($id);
    }

    public function save(Structure $structure)
    {
        $this->store->setItem($structure->handle(), $structure);

        $this->store->save($structure);
    }

    public function delete(Structure $structure)
    {
        $this->store->removeItem($structure->handle());

        $this->store->delete($structure);
    }

    public function make()
    {
        return new \Statamic\Data\Structures\Structure;
    }

    public function updateEntryUris(Structure $structure)
    {
        $this->store->updateEntryUris($structure);
    }
}

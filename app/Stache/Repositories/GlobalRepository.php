<?php

namespace Statamic\Stache\Repositories;

use Statamic\Stache\Stache;
use Statamic\Data\Globals\GlobalCollection;
use Statamic\Contracts\Data\Globals\GlobalSet;
use Illuminate\Support\Collection as IlluminateCollection;
use Statamic\Contracts\Data\Repositories\GlobalRepository as RepositoryContract;

class GlobalRepository implements RepositoryContract
{
    protected $stache;
    protected $store;

    public function __construct(Stache $stache)
    {
        $this->stache = $stache;
        $this->store = $stache->store('globals');
    }

    public function make()
    {
        return new \Statamic\Data\Globals\GlobalSet;
    }

    public function all(): GlobalCollection
    {
        return collect_globals($this->store->getItems());
    }

    public function find($id): ?GlobalSet
    {
        return $this->store->getItem($id);
    }

    public function findByHandle($handle): ?GlobalSet
    {
        return $this->find($this->store->getIdByHandle($handle));
    }

    public function save($global)
    {
        if (! $global->id()) {
            $global->id($this->stache->generateId());
        }

        // TODO: Ensure changes to entry after saving aren't persisted at the end of the request.

        $this->store->insert($global);

        $this->store->save($global);
    }
}

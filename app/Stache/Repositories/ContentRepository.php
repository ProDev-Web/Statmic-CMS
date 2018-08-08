<?php

namespace Statamic\Stache\Repositories;

use Statamic\Stache\Stache;
use Statamic\Contracts\Data\Content\Content;
use Statamic\Data\Content\ContentCollection;
use Statamic\Contracts\Data\Repositories\EntryRepository;
use Statamic\Contracts\Data\Repositories\ContentRepository as RepositoryContract;

class ContentRepository implements RepositoryContract
{
    protected $stache;

    public function __construct(Stache $stache)
    {
        $this->stache = $stache;
    }

    public function all(): ContentCollection
    {
        return app(EntryRepository::class)->all();
    }

    public function find($id): ?Content
    {
        if (! $store = $this->stache->getStoreById($id)) {
            return null;
        }

        return $store->getItem($id);
    }
}

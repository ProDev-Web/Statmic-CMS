<?php

namespace Statamic\Stache\Repositories;

use Statamic\Facades;
use Statamic\Facades\Str;
use Statamic\Stache\Stache;
use Illuminate\Support\Collection;
use Statamic\Contracts\Data\Taxonomies\Taxonomy;
use Statamic\Contracts\Data\Repositories\TaxonomyRepository as RepositoryContract;

class TaxonomyRepository implements RepositoryContract
{
    protected $store;

    public function __construct(Stache $stache)
    {
        $this->store = $stache->store('taxonomies');
    }

    public function all(): Collection
    {
        return $this->store->getItems($this->handles());
    }

    public function find($id): ?Taxonomy
    {
        return $this->findByHandle($id);
    }

    public function handles(): Collection
    {
        return $this->store->paths()->keys();
    }

    public function findByHandle($handle): ?Taxonomy
    {
        return $this->store->getItem($handle);
    }

    public function save(Taxonomy $taxonomy)
    {
        $this->store->save($taxonomy);
    }

    public function delete(Taxonomy $taxonomy)
    {
        $this->store->delete($taxonomy);
    }

    public function make($handle = null): Taxonomy
    {
        return app(Taxonomy::class)->handle($handle);
    }

    public function findByUri(string $uri, string $site = null): ?Taxonomy
    {
        $collection = Facades\Collection::all()
            ->filter->url()
            ->first(function ($collection) use ($uri) {
                return Str::startsWith($uri, $collection->url());
            });

        if ($collection) {
            $uri = Str::after($uri, $collection->url());
        }

        if (! $key = $this->store->index('uri')->items()->flip()->get($uri)) {
            return null;
        }

        return $this->findByHandle($key)->collection($collection);
    }
}

<?php

namespace Statamic\Providers;

use Statamic\API\Site;
use Statamic\API\Term;
use Statamic\API\Entry;
use Statamic\API\Taxonomy;
use Statamic\API\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->bindEntries();
        $this->bindCollections();
        $this->bindTerms();
        $this->bindTaxonomies();
        $this->bindSites();
        $this->bindRevisions();
    }

    protected function bindEntries()
    {
        Route::bind('entry', function ($entry, $route) {
            abort_if(
                ! ($entry = Entry::find($entry))
                || $entry->collection() !== $route->parameter('collection')
            , 404);

            return $entry;
        });
    }

    protected function bindCollections()
    {
        Route::bind('collection', function ($collection) {
            abort_if(! $collection = Collection::findByHandle($collection), 404);
            return $collection;
        });
    }

    protected function bindTaxonomies()
    {
        Route::bind('taxonomy', function ($taxonomy) {
            abort_if(! $taxonomy = Taxonomy::findByHandle($taxonomy), 404);
            return $taxonomy;
        });
    }

    protected function bindTerms()
    {
        Route::bind('term', function ($term, $route) {
            $id = $route->parameter('taxonomy')->handle() . '::' . $term;
            abort_if(
                ! ($term = Term::find($id))
                || $term->taxonomy() !== $route->parameter('taxonomy')
            , 404);

            return $term;
        });
    }

    protected function bindSites()
    {
        Route::bind('site', function ($site) {
            abort_if(! $site = Site::get($site), 404);
            return $site;
        });
    }

    protected function bindRevisions()
    {
        Route::bind('revision', function ($revision, $route) {
            if ($route->hasParameter('entry')) {
                $content = $route->parameter('entry');
            } elseif ($route->hasParameter('term')) {
                $content = $route->parameter('term');
            } else {
                abort(404);
            }

            abort_if(! $revision = $content->revision($revision), 404);

            return $revision;
        });
    }
}

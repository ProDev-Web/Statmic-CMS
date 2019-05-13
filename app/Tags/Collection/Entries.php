<?php

namespace Statamic\Tags\Collection;

use Closure;
use Statamic\API;
use Statamic\API\Arr;
use Statamic\API\Entry;
use Statamic\Query\OrderBy;
use Statamic\API\Collection;
use Illuminate\Support\Carbon;
use Statamic\Tags\GetsQueryResults;

class Entries
{
    use GetsQueryResults, HasConditions;

    protected $collections;
    protected $ignoredParams = ['as'];
    protected $parameters;
    protected $site;
    protected $limit;
    protected $offset;
    protected $paginate;
    protected $showPublished;
    protected $showUnpublished;
    protected $showPast;
    protected $since;
    protected $until;
    protected $scopes;
    protected $orderBys;

    public function __construct($parameters)
    {
        $this->parameters = $this->parseParameters($parameters);
    }

    public function get()
    {
        try {
            $query = $this->query();
        } catch (NoResultsExpected $exception) {
            return collect_entries();
        }

        return $this->results($query);
    }

    public function count()
    {
        try {
            return $this->query()->count();
        } catch (NoResultsExpected $exception) {
            return 0;
        }
    }

    public function next($currentEntry)
    {
        $pagination = $this->parsePaginationParameters($this->parameters);

        throw_if($pagination['paginate'], new \Exception('collection:next is not compatible with [paginate] parameter'));
        throw_if($pagination['offset'], new \Exception('collection:next is not compatible with [offset] parameter'));

        // TODO: but only if all collections have the same configuration.
        $collection = $this->collections[0];

        if ($this->orderBys->first()->direction === 'desc') {
            $this->orderBys = $this->orderBys->map->reverse();
        }

        if ($collection->orderable()) {
            $query = $this->query()->where('order', '>', $currentEntry->order());
        } elseif ($collection->dated()) {
            $query = $this->query()->where('date', '>', $currentEntry->date());
        } else {
            throw new \Exception('collection:next requires ordered or dated collection');
        }

        return $this->results($query);
    }

    public function previous($currentEntry)
    {
        $pagination = $this->parsePaginationParameters($this->parameters);

        throw_if($pagination['paginate'], new \Exception('collection:previous is not compatible with [paginate] parameter'));
        throw_if($pagination['offset'], new \Exception('collection:previous is not compatible with [offset] parameter'));

        // TODO: but only if all collections have the same configuration.
        $collection = $this->collections[0];

        if ($this->orderBys->first()->direction === 'asc') {
            $this->orderBys = $this->orderBys->map->reverse();
        }

        if ($collection->orderable()) {
            $query = $this->query()->where('order', '<', $currentEntry->order());
        } elseif ($collection->dated()) {
            $query = $this->query()->where('date', '<', $currentEntry->date());
        } else {
            throw new \Exception('collection:previous requires ordered or dated collection');
        }

        return $this->results($query)->reverse()->values();
    }

    protected function query()
    {
        $query = Entry::query()
            ->whereIn('collection', $this->collections->map->handle()->all());

        $this->querySite($query);
        $this->queryPublished($query);
        $this->queryPastFuture($query);
        $this->querySinceUntil($query);
        $this->queryConditions($query);
        $this->queryScopes($query);
        $this->queryOrderBys($query);

        return $query;
    }

    protected function parseParameters($params)
    {
        $params = Arr::except($params, $this->ignoredParams);

        $this->collections = $this->parseCollections($params);
        $this->site = Arr::getFirst($params, ['site', 'locale']);

        $this->showPublished = Arr::get($params, 'show_published', true);
        $this->showUnpublished = Arr::get($params, 'show_unpublished', false);
        $this->since = Arr::get($params, 'since');
        $this->until = Arr::get($params, 'until');

        $this->scopes = $this->parseQueryScopes($params);
        $this->orderBys = $this->parseOrderBys($params);

        return $params;
    }

    protected function parseCollections($params)
    {
        $from = Arr::getFirst($params, ['from', 'in', 'folder', 'use', 'collection']);
        $not = Arr::getFirst($params, ['not_from', 'not_in', 'not_folder', 'dont_use', 'not_collection']);

        $collections = $from === '*'
            ? collect(Collection::handles())
            : collect(explode('|', $from));

        $excludedCollections = collect(explode('|', $not))->filter();

        return $collections
            ->diff($excludedCollections)
            ->map(function ($handle) {
                $collection = Collection::whereHandle($handle);
                throw_unless($collection, new \Exception("Collection [{$handle}] does not exist."));
                return $collection;
            })
            ->values();
    }

    protected function parseQueryScopes($params)
    {
        $scopes = Arr::getFirst($params, ['query', 'filter']);

        return collect(explode('|', $scopes));
    }

    protected function parseOrderBys($params)
    {
        $piped = Arr::getFirst($params, ['order_by', 'sort']) ?? $this->parseDefaultOrderBy();

        return collect(explode('|', $piped))->filter()->map(function ($orderBy) {
            return OrderBy::parse($orderBy);
        });
    }

    protected function parseDefaultOrderBy()
    {
        // TODO: but only if all collections have the same configuration.
        $collection = $this->collections[0];

        if ($collection->orderable()) {
            return 'order:asc';
        } elseif ($collection->dated()) {
            return 'date:desc|title:asc';
        }

        return 'title:asc';
    }

    protected function querySite($query)
    {
        if (! $this->site) {
            return;
        }

        return $query->where('site', $this->site);
    }

    protected function queryPublished($query)
    {
        if ($this->showPublished && $this->showUnpublished) {
            return;
        } elseif ($this->showPublished && ! $this->showUnpublished) {
            return $query->where('published', true);
        } elseif (! $this->showPublished && $this->showUnpublished) {
            return $query->where('published', false);
        }

        throw new NoResultsExpected;
    }

    protected function queryPastFuture($query)
    {
        if (! $this->allCollectionsAreDates()) {
            return;
        }

        // Collection date behaviors
        // TODO: but only if all collections have the same configuration.
        $collection = $this->collections[0];
        $showFuture = $collection->futureDateBehavior() === 'public';
        $showPast = $collection->pastDateBehavior() === 'public';

        // Override by tag parameters.
        $showFuture = $this->parameters['show_future'] ?? $showFuture;
        $showPast = $this->parameters['show_past'] ?? $showPast;

        if ($showFuture && $showPast) {
            return;
        } elseif ($showFuture && ! $showPast) {
            return $query->where('date', '>', Carbon::now());
        } elseif (! $showFuture && $showPast) {
            return $query->where('date', '<', Carbon::now());
        }

        throw new NoResultsExpected;
    }

    protected function querySinceUntil($query)
    {
        if (! $this->allCollectionsAreDates()) {
            return;
        }

        if ($this->since) {
            $query->where('date', '>', Carbon::parse($this->since));
        }

        if ($this->until) {
            $query->where('date', '<', Carbon::parse($this->until));
        }
    }

    public function queryScopes($query)
    {
        $this->scopes
            ->map(function ($handle) {
                return app('statamic.scopes')->get($handle);
            })
            ->filter()
            ->each(function ($class) use ($query) {
                $scope = app($class);
                $scope->apply($query, $this->parameters);
            });
    }

    public function queryOrderBys($query)
    {
        $this->orderBys->each(function ($orderBy) use ($query) {
            $query->orderBy($orderBy->sort, $orderBy->direction);
        });
    }

    protected function allCollectionsAreDates()
    {
        return $this->allCollectionsAre(function ($collection) {
            return $collection->dated();
        });
    }

    protected function allCollectionsAre(Closure $condition)
    {
        return $this->collections->reject(function ($collection) use ($condition) {
            return $condition($collection);
        })->isEmpty();
    }
}

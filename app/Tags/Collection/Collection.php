<?php

namespace Statamic\Tags\Collection;

use Statamic\API;
use Statamic\Tags\Tags;
use Statamic\Data\Entries\EntryCollection;
use Illuminate\Contracts\Pagination\Paginator;

class Collection extends Tags
{
    /**
     * {{ collection:* }} ... {{ /collection:* }}
     */
    public function __call($method, $args)
    {
        $this->parameters['from'] = $this->method;

        return $this->index();
    }

    /**
     * {{ collection from="" }} ... {{ /collection }}
     */
    public function index()
    {
        $this->entries = (new Entries($this->parameters))->get();

        return $this->output();
    }

    public function count()
    {
        return (new Entries($this->parameters))->count();
    }

    protected function output()
    {
        if ($this->entries instanceof Paginator) {
            return $this->paginatedOutput();
        }

        if ($as = $this->get('as')) {
            return [$as => $this->entries];
        }

        return $this->entries;
    }

    protected function paginatedOutput()
    {
        $as = $this->get('as', 'entries');
        $paginator = $this->entries;
        $entries = $paginator->getCollection()->supplement('total_results', $paginator->total());

        return [
            $as => $entries,
            'paginate' => $this->getPaginationData($paginator),
            'total_results' => 10,
        ];
    }

    protected function getPaginationData($paginator)
    {
        return [
            'total_items'    => $paginator->total(),
            'items_per_page' => $paginator->perPage(),
            'total_pages'    => $paginator->lastPage(),
            'current_page'   => $paginator->currentPage(),
            'prev_page'      => $paginator->previousPageUrl(),
            'next_page'      => $paginator->nextPageUrl(),
            'auto_links'     => $paginator->render('pagination::default'),
            'links'          => $paginator->renderArray()
        ];
    }
}

<?php

namespace Statamic\Fields\Fieldtypes;

use Statamic\CP\Column;
use Statamic\API\Collection;

class Collections extends Relationship
{
    protected $canEdit = false;
    protected $canCreate = false;
    protected $canSearch = false;
    protected $statusIcons = false;

    protected function toItemArray($id, $site = null)
    {
        if ($collection = Collection::whereHandle($id)) {
            return [
                'title' => $collection->title(),
                'id' => $collection->handle(),
            ];
        }

        return $this->invalidItemArray($id);
    }

    public function getIndexItems($request)
    {
        return Collection::all()->map(function ($collection) {
            return [
                'id' => $collection->handle(),
                'title' => $collection->title(),
                'entries' => $collection->queryEntries()->count(),
            ];
        })->values();
    }

    protected function getColumns()
    {
        return [
            Column::make('title'),
            Column::make('entries'),
        ];
    }

    protected function augmentValue($value)
    {
        return Collection::whereHandle($value);
    }
}

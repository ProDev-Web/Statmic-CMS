<?php

namespace Statamic\Fieldtypes;

use Statamic\CP\Column;
use Statamic\Facades\Blueprint;

class Blueprints extends Relationship
{
    protected $canEdit = false;
    protected $canCreate = false;
    protected $statusIcons = false;

    protected function toItemArray($id, $site = null)
    {
        if ($blueprint = Blueprint::find($id)) {
            return [
                'title' => $blueprint->title(),
                'id' => $blueprint->handle(),
            ];
        }

        return $this->invalidItemArray($id);
    }

    public function getIndexItems($request)
    {
        return Blueprint::all()->map(function ($blueprint) {
            return [
                'id' => $blueprint->handle(),
                'title' => $blueprint->title(),
            ];
        })->values();
    }

    public function augmentValue($value)
    {
        return Blueprint::find($value);
    }

    protected function getColumns()
    {
        return [
            Column::make('title'),
        ];
    }
}

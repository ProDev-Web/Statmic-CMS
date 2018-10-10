<?php

namespace Statamic\Addons\Lists;

use Statamic\API\Helper;
use Statamic\Fields\Fieldtype;

class ListsFieldtype extends Fieldtype
{
    protected $handle = 'list';

    public function preProcess($data)
    {
        if (is_null($data)) {
            return [];
        }

        return Helper::ensureArray($data);
    }

    public function process($data)
    {
        if (! is_array($data)) {
            return $data;
        }

        return collect($data)->reject(function ($item) {
            return in_array($item, [null, ''], true);
        })->values()->all();
    }
}

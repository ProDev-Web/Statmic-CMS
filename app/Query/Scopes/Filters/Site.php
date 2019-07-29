<?php

namespace Statamic\Query\Scopes\Filters;

use Statamic\API;
use Statamic\Query\Scopes\Filter;

class Site extends Filter
{
    public function fieldItems()
    {
        $options = API\Site::all()->mapWithKeys(function ($site) {
            return [$site->handle() => $site->name()];
        })->all();

        return [
            'value' => [
                'display' => __('Site'),
                'type' => 'select',
                'options' => $options
            ]
        ];
    }

    public function apply($query, $values)
    {
        $query->where('site', $values['value']);
    }

    public function required()
    {
        return true;
    }

    public function visibleTo($key)
    {
        if (! API\Site::hasMultiple()) {
            return false;
        }

        return $key === 'entries';
    }
}

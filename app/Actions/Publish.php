<?php

namespace Statamic\Actions;

use Statamic\API;
use Statamic\API\User;
use Statamic\API\Collection;
use Statamic\Contracts\Data\Entries\Entry;

class Publish extends Action
{
    public function visibleTo($key, $context)
    {
        return $key === 'entries';
    }

    public function authorize($entry)
    {
        return user()->can('publish', [Entry::class, $entry->collection()]);
    }

    public function run($entries)
    {
        $entries->each(function ($entry) {
            $entry->publish(['user' => User::current()]);
        });
    }
}

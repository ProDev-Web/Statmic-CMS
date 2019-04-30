<?php

namespace Statamic\Data\Entries;

use Closure;
use Statamic\API;
use Statamic\API\Site;
use Statamic\Data\Localizable;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Exceptions\InvalidLocalizationException;
use Statamic\Contracts\Data\Entries\Entry as Contract;

class Entry implements Contract, Augmentable
{
    use Localizable;

    protected $id;
    protected $collection;

    public function id($id = null)
    {
        if (is_null($id)) {
            return $this->id;
        }

        $this->id = $id;

        $this->localizations()->each->id($id);

        return $this;
    }

    public function collection($collection = null)
    {
        if (is_null($collection)) {
            return $this->collection;
        }

        $this->collection = $collection;

        return $this;
    }

    public function collectionHandle()
    {
        return $this->collection->handle();
    }

    public function toCacheableArray()
    {
        return [
            'collection' => $this->collectionHandle(),
            'localizations' => $this->localizations()->map(function ($entry) {
                return [
                    'slug' => $entry->slug(),
                    'date' => optional($entry->date())->format('Y-m-d-Hi'),
                    'published' => $entry->published(),
                    'path' => $entry->initialPath() ?? $entry->path(),
                    'data' => $entry->data()
                ];
            })->all()
        ];
    }

    protected function makeLocalization()
    {
        return new LocalizedEntry;
    }

    public function toAugmentedArray()
    {
        return $this->forCurrentSite()->toAugmentedArray();
    }

    public function slug($slug = null)
    {
        return call_user_func_array([$this->forCurrentSite(), 'slug'], func_get_args());
    }

    public function date($date = null)
    {
        return call_user_func_array([$this->forCurrentSite(), 'date'], func_get_args());
    }

    public function published($published = null)
    {
        return call_user_func_array([$this->forCurrentSite(), 'published'], func_get_args());
    }

    public function order($order = null)
    {
        return call_user_func_array([$this->forCurrentSite(), 'order'], func_get_args());
    }

    public function delete()
    {
        API\Entry::deleteLocalizable($this);

        return true;
    }
}

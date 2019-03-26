<?php

namespace Statamic\Data\Entries;

use Statamic\API;
use Statamic\API\Site;
use Statamic\API\Stache;
use Statamic\API\Blueprint;
use Statamic\Data\Routable;
use Illuminate\Support\Carbon;
use Statamic\Data\Augmentable;
use Statamic\Data\Publishable;
use Statamic\Data\ContainsData;
use Statamic\Data\Localization;
use Statamic\Data\ExistsAsFile;
use Statamic\Events\Data\EntrySaved;
use Statamic\Events\Data\EntrySaving;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Responsable;
use Statamic\Contracts\Data\Entries\LocalizedEntry as Contract;
use Statamic\Contracts\Data\Augmentable as AugmentableContract;
use Statamic\Contracts\Data\Localization as LocalizationContract;

class LocalizedEntry implements Contract, Arrayable, AugmentableContract, Responsable, LocalizationContract
{
    use Routable, Localization, ContainsData, ExistsAsFile, Publishable, Augmentable;

    protected $order;

    public function entry($entry = null)
    {
        return call_user_func_array([$this, 'localizable'], func_get_args());
    }

    public function collection()
    {
        return $this->entry()->collection();
    }

    public function collectionHandle()
    {
        return $this->collection()->handle();
    }

    public function toArray()
    {
        return array_merge($this->data, [
            'id' => $this->id(),
            'slug' => $this->slug(),
            'uri' => $this->uri(),
            'url' => $this->url(),
            'edit_url' => $this->editUrl(),
            'permalink' => $this->absoluteUrl(),
            'amp_url' => $this->ampUrl(),
            'published' => $this->published(),
            'date' => $this->date(),
            'is_entry' => true,
            'collection' => $this->collectionHandle(),
        ], $this->supplements);
    }

    public function editUrl()
    {
        return $this->cpUrl('collections.entries.edit');
    }

    public function updateUrl()
    {
        return $this->cpUrl('collections.entries.update');
    }

    protected function cpUrl($route)
    {
        return cp_route($route, [$this->collectionHandle(), $this->id(), $this->slug(), $this->locale()]);
    }

    public function blueprint()
    {
        if ($blueprint = $this->get('blueprint')) {
            return $this->collection()->ensureEntryBlueprintFields(
                Blueprint::find($blueprint)
            );
        }

        return $this->collection()->entryBlueprint();
    }

    public function save()
    {
        if (EntrySaving::dispatch($this) === false) {
            return false;
        }

        API\Entry::save($this);

        $this->entry()->addLocalization($this);

        if ($this->shouldPropagate) {
            $this->propagate();
        }

        EntrySaved::dispatch($this, []);  // TODO: Fix test

        return true;
    }

    public function path()
    {
        $prefix = '';

        if ($order = $this->order()) {
            $prefix = $order . '.';
        }

        return vsprintf('%s/%s/%s%s%s.%s', [
            rtrim(Stache::store('entries')->directory(), '/'),
            $this->collectionHandle(),
            Site::hasMultiple() ? $this->locale().'/' : '',
            $prefix,
            $this->slug(),
            'md'
        ]);
    }

    public function orderType()
    {
        return $this->collection()->order();
    }

    public function order($order = null)
    {
        if (func_num_args() === 0) {
            return $this->order;
        }

        $this->order = $order;

        return $this;
    }

    public function supplementTaxonomies()
    {
        // Added this method because a bunch of things call it.
        // Rather than update those things right now, just add this so things continue to hum along.
        // TODO: Get rid of this during taxonomy refactor.
    }

    public function template($template = null)
    {
        if (func_num_args() === 0) {
            return $this->template ?? $this->collection()->template();
        }

        $this->template = $template;

        return $this;
    }

    public function layout($layout = null)
    {
        if (func_num_args() === 0) {
            return $this->layout ?? $this->collection()->layout();
        }

        $this->layout = $layout;

        return $this;
    }

    public function toResponse($request)
    {
        return (new \Statamic\Http\Responses\DataResponse($this))->toResponse($request);
    }

    public function toLivePreviewResponse($request)
    {
        return $this->toResponse($request);
    }

    public function date()
    {
        if ($this->orderType() === 'date') {
            return $this->hasTime()
                ? Carbon::createFromFormat('Y-m-d-Hi', $this->order())
                : Carbon::createFromFormat('Y-m-d', $this->order())->startOfDay();
        }

        return null;
    }

    public function hasTime()
    {
        return $this->orderType() === 'date' && strlen($this->order()) === 15;
    }

    public function sites()
    {
        return $this->collection()->sites();
    }

    protected function fileData()
    {
        return array_merge($this->data(), [
            'id' => $this->id(),
            'published' => $this->published === false ? false : null
        ]);
    }

    public function ampable()
    {
        return $this->collection()->ampable();
    }
}

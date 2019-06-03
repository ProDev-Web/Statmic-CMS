<?php

namespace Statamic\Data\Globals;

use Statamic\API\Site;
use Statamic\API\Stache;
use Statamic\API\GlobalSet;
use Statamic\Data\HasOrigin;
use Statamic\Data\Augmentable;
use Statamic\Data\ContainsData;
use Statamic\Data\ExistsAsFile;
use Statamic\FluentlyGetsAndSets;
use Statamic\Contracts\Data\Localization;
use Statamic\Contracts\Data\Augmentable as AugmentableContract;
use Statamic\Contracts\Data\Globals\LocalizedGlobalSet as Contract;

class Variables implements Contract, Localization, AugmentableContract
{
    use ExistsAsFile, ContainsData, Augmentable, HasOrigin, FluentlyGetsAndSets;

    protected $set;
    protected $locale;

    public function globalSet($set = null)
    {
        return $this->fluentlyGetOrSet('set')->args(func_get_args());
    }

    public function locale($locale = null)
    {
        return $this->fluentlyGetOrSet('locale')->args(func_get_args());
    }

    public function id()
    {
        return $this->globalSet()->id();
    }

    public function handle()
    {
        return $this->globalSet()->handle();
    }

    public function title()
    {
        return $this->globalSet()->title();
    }

    public function path()
    {
        return vsprintf('%s/%s%s.%s', [
            rtrim(Stache::store('globals')->directory(), '/'),
            Site::hasMultiple() ? $this->locale().'/' : '',
            $this->handle(),
            'yaml'
        ]);
    }

    public function editUrl()
    {
        return $this->cpUrl('globals.edit');
    }

    public function updateUrl()
    {
        return $this->cpUrl('globals.update');
    }

    protected function cpUrl($route)
    {
        $params = [$this->id(), $this->handle()];

        if (Site::hasMultiple()) {
            $params['site'] = $this->locale();
        }

        return cp_route($route, $params);
    }

    public function save()
    {
        $this
            ->globalSet()
            ->addLocalization($this)
            ->save();

        return $this;
    }

    public function site()
    {
        return Site::get($this->locale());
    }

    public function sites()
    {
        return $this->globalSet()->sites();
    }

    public function blueprint()
    {
        return $this->globalSet()->blueprint() ?? $this->fallbackBlueprint();
    }

    protected function fallbackBlueprint()
    {
        $fields  = collect($this->values())
            ->except(['id', 'title', 'blueprint'])
            ->map(function ($field, $handle) {
                return [
                    'handle' => $handle,
                    'field' => ['type' => 'text'],
                ];
            });

        return (new \Statamic\Fields\Blueprint)->setContents([
            'sections' => [
                'main' => [
                    'fields' => $fields->all()
                ]
            ]
        ]);
    }

    public function toArray()
    {
        return array_merge($this->values(), [
            'id' => $this->id(),
            'handle' => $this->handle(),
        ], $this->supplements);
    }

    public function fileData()
    {
        return array_merge([
            'origin' => $this->hasOrigin() ? $this->origin->locale() : null,
        ], $this->data());
    }

    protected function augmentedArrayData()
    {
        return $this->values();
    }

    public function reference()
    {
        return "globals::{$this->id()}";
    }
}

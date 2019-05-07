<?php

namespace Statamic\Data\Globals;

use Statamic\API\Site;
use Statamic\API\Stache;
use Statamic\API\GlobalSet;
use Statamic\Data\Augmentable;
use Statamic\Data\ContainsData;
use Statamic\Data\ExistsAsFile;
use Statamic\Data\Localization;
use Statamic\Contracts\Data\Augmentable as AugmentableContract;
use Statamic\Contracts\Data\Localization as LocalizationContract;
use Statamic\Contracts\Data\Globals\LocalizedGlobalSet as Contract;
use Statamic\Data\HasOrigin;

class Variables implements Contract, LocalizationContract, AugmentableContract
{
    use Localization, ExistsAsFile, ContainsData, Augmentable, HasOrigin;

    public function handle()
    {
        return $this->localizable()->handle();
    }

    public function title()
    {
        return $this->localizable()->title();
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
        return cp_route('globals.edit', [
            $this->id(),
            $this->handle(),
            $this->locale(),
        ]);
    }

    public function updateUrl()
    {
        return cp_route('globals.update', [
            $this->id(),
            $this->handle(),
            $this->locale(),
        ]);
    }

    public function save()
    {
        GlobalSet::save($this);

        $this->localizable()->addLocalization($this);

        if ($this->shouldPropagate) {
            $this->propagate();
        }

        // GlobalSetSaved::dispatch($this, []);  // TODO

        return $this;
    }

    public function sites()
    {
        return Site::all()->map->handle();
        // TODO: Global should be able to control which sites it can be localized into.
    }

    public function blueprint()
    {
        return $this->localizable()->blueprint();
    }

    public function toArray()
    {
        return array_merge($this->values(), [
            'id' => $this->id(),
            'handle' => $this->handle(),
        ], $this->supplements);
    }

    protected function fileData()
    {
        return $this->data();
    }

    protected function augmentedArrayData()
    {
        return $this->values();
    }
}

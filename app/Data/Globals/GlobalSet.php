<?php

namespace Statamic\Data\Globals;

use Statamic\API;
use Statamic\API\Site;
use Statamic\API\Stache;
use Statamic\API\Blueprint;
use Statamic\Data\ExistsAsFile;
use Statamic\FluentlyGetsAndSets;
use Statamic\Contracts\Data\Globals\GlobalSet as Contract;

class GlobalSet implements Contract
{
    use ExistsAsFile, FluentlyGetsAndSets;

    protected $id;
    protected $title;
    protected $handle;
    protected $sites;
    protected $blueprint;
    protected $localizations;

    public function id($id = null)
    {
        return $this->fluentlyGetOrSet('id')->args(func_get_args());
    }

    public function sites($sites = null)
    {
        return $this
            ->fluentlyGetOrSet('sites')
            ->getter(function ($sites) {
                return collect(Site::hasMultiple() ? $sites : [Site::default()->handle()]);
            })
            ->args(func_get_args());
    }

    public function handle($handle = null)
    {
        return $this->fluentlyGetOrSet('handle')->args(func_get_args());
    }

    public function title($title = null)
    {
        return $this
            ->fluentlyGetOrSet('title')
            ->getter(function ($title) {
                return $title ?? ucfirst($this->handle);
            })
            ->args(func_get_args());
    }

    public function blueprint($blueprint = null)
    {
        return $this->fluentlyGetOrSet('blueprint')
            ->getter(function ($blueprint) {
                return Blueprint::find($blueprint);
            })
            ->args(func_get_args());
    }

    public function path()
    {
        return vsprintf('%s/%s.%s', [
            rtrim(Stache::store('globals')->directory(), '/'),
            $this->handle(),
            'yaml'
        ]);
    }

    public function toCacheableArray()
    {
        return [
            'handle' => $this->handle,
            'title' => $this->title,
            'blueprint' => $this->blueprint,
            'sites' => $this->sites()->all(),
            'path' => $this->path(),
            'localizations' => $this->localizations()->map(function ($localized) {
                return [
                    'path' => $localized->initialPath() ?? $localized->path(),
                    'data' => $localized->data()
                ];
            })->all()
        ];
    }

    public function save()
    {
        API\GlobalSet::save($this);

        return $this;
    }

    protected function fileData()
    {
        $data = [
            'id' => $this->id,
            'title' => $this->title(),
            'blueprint' => $this->blueprint,
        ];

        if (Site::hasMultiple()) {
            $data['sites'] = $this->sites()->all();
        } else {
            $data['data'] = $this->data();
        }

        return $data;
    }

    public function makeLocalization($site, $origin = null)
    {
        return (new Variables)
            ->globalSet($this)
            ->locale($site);
    }

    public function addLocalization($localization)
    {
        $localization->globalSet($this);

        $this->localizations[$localization->locale()] = $localization;

        return $this;
    }

    public function in($locale)
    {
        return $this->localizations[$locale] ?? null;
    }

    public function inSelectedSite()
    {
        return $this->in(Site::selected()->handle());
    }

    public function existsIn($locale)
    {
        return $this->in($locale) !== null;
    }

    public function localizations()
    {
        return collect($this->localizations);
    }

    public function toArray()
    {
        throw new \Exception('GlobalSet toArray');
    }
}

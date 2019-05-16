<?php

namespace Statamic\Data\Structures;

use Statamic\API\Site;
use Statamic\API\Entry;
use Statamic\API\Stache;
use Statamic\Data\ExistsAsFile;
use Statamic\FluentlyGetsAndSets;
use Statamic\API\Structure as StructureAPI;
use Statamic\Contracts\Data\Structures\Structure as StructureContract;

class Structure implements StructureContract
{
    use FluentlyGetsAndSets, ExistsAsFile;

    protected $title;
    protected $handle;
    protected $sites;
    protected $trees;

    public function id()
    {
        return $this->handle();
    }

    public function handle($handle = null)
    {
        if (is_null($handle)) {
            return $this->handle;
        }

        $this->handle = $handle;

        return $this;
    }

    public function title($title = null)
    {
        return $this->fluentlyGetOrSet('title')->args(func_get_args());
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

    public function showUrl()
    {
        return cp_route('structures.show', $this->handle());
    }

    public function editUrl()
    {
        return cp_route('structures.edit', $this->handle());
    }

    public function save()
    {
        StructureAPI::save($this);
    }

    public function toCacheableArray()
    {
        return [
            'title' => $this->title,
            'handle' => $this->handle,
            'sites' => $this->sites,
            'path' => $this->initialPath() ?? $this->path(),
            'trees' => $this->trees()->map->toCacheableArray()->all()
        ];
    }

    public function path()
    {
        return vsprintf('%s/%s.yaml', [
            rtrim(Stache::store('structures')->directory(), '/'),
            $this->handle
        ]);
    }

    public function fileData()
    {
        $data = [
            'title' => $this->title,
            'sites' => $this->sites,
        ];

        if (! Site::hasMultiple()) {
            $data['tree'] = $this->in($this->locale())->toArray();
        }

        return $data;
    }

    public function trees()
    {
        return collect($this->trees);
    }

    public function makeTree($site)
    {
        return (new Tree)
            ->locale($site)
            ->structure($this);
    }

    public function addTree($tree)
    {
        $tree->structure($this);

        $this->trees[$tree->locale()] = $tree;

        return $this;
    }

    public function removeTree($tree)
    {
        unset($this->trees[$tree->locale()]);

        return $this;
    }

    public function existsIn($site)
    {
        return isset($this->trees[$site]);
    }

    public function in($site)
    {
        return $this->trees[$site] ?? null;
    }
}

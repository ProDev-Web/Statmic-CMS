<?php

namespace Statamic\Http\Controllers\CP\Structures;

use Statamic\API\Arr;
use Statamic\API\Site;
use Statamic\API\Structure;
use Illuminate\Http\Request;
use Statamic\Data\Structures\TreeBuilder;
use Statamic\Http\Controllers\CP\CpController;

class StructurePagesController extends CpController
{
    public function index(Request $request, $structure)
    {
        $structure = Structure::find($structure);
        $site = $request->site ?? Site::selected()->handle();

        $pages = (new TreeBuilder)->buildForController([
            'structure' => $structure->handle(),
            'include_home' => true,
            'site' => $site,
        ]);

        return ['pages' => $pages];
    }

    public function store(Request $request, $structure)
    {
        $tree = $this->toTree($request->pages);

        if ($request->firstPageIsRoot) {
            $root = array_pull($tree, 0)['entry'];
            $tree = array_values($tree);
        }

        $tree = Structure::find($structure)
            ->in($request->site)
            ->root($root ?? null)
            ->tree($tree);

        $tree->save();
    }

    protected function toTree($items)
    {
        return collect($items)->map(function ($item) {
            return Arr::removeNullValues([
                'entry' => $ref = $item['id'] ?? null,
                'title' => $ref ? null : ($item['title'] ?? null),
                'url' => $ref ? null : ($item['url'] ?? null),
                'children' => $this->toTree($item['children'])
            ]);
        })->all();
    }
}

<?php

namespace Statamic\Http\Controllers\CP\Structures;

use Statamic\API\Site;
use Statamic\API\Blueprint;
use Statamic\API\Structure;
use Illuminate\Http\Request;
use Statamic\API\Collection;
use Statamic\Fields\Validation;
use Statamic\Data\Structures\TreeBuilder;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Contracts\Data\Structures\Structure as StructureContract;

class StructuresController extends CpController
{
    public function index()
    {
        $this->authorize('index', StructureContract::class, 'You are not authorized to view any structures.');

        $structures = Structure::all()->filter(function ($structure) {
            return me()->can('view', $structure);
        })->map(function ($structure) {
            $tree = $structure->in(Site::selected()->handle());

            return [
                'id' => $structure->handle(),
                'title' => $structure->title(),
                'show_url' => $tree->editUrl(),
                'edit_url' => $structure->editUrl(),
                'deleteable' => me()->can('delete', $structure)
            ];
        })->values();

        return view('statamic::structures.index', compact('structures'));
    }

    public function edit($structure)
    {
        $structure = Structure::find($structure);

        $this->authorize('edit', $structure, 'You are not authorized to edit this structure.');

        $values = [
            'title' => $structure->title(),
            'handle' => $structure->handle(),
            'sites' => Site::all()->map(function ($site) use ($structure) {
                $tree = $structure->in($site->handle());
                return [
                    'name' => $site->name(),
                    'handle' => $site->handle(),
                    'enabled' => $enabled = $tree !== null,
                    'route' => $enabled ? $tree->route() : null,
                    'inherit' => false,
                ];
            })->values()->all()
        ];

        $fields = ($blueprint = $this->editFormBlueprint())
            ->fields()
            ->addValues($values)
            ->preProcess();

        return view('statamic::structures.edit', [
            'blueprint' => $blueprint->toPublishArray(),
            'values' => $fields->values(),
            'meta' => $fields->meta(),
            'structure' => $structure,
        ]);
    }

    public function show(Request $request, $structure)
    {
        $structure = Structure::find($structure);
        $site = $request->site ?? Site::selected()->handle();

        if (! $tree = $structure->in($site)) {
            return abort(404);
        }

        $pages = (new TreeBuilder)->buildForController([
            'structure' => $structure->handle(),
            'include_home' => true,
            'site' => $site,
        ]);

        return view('statamic::structures.show', [
            'site' => $site,
            'structure' => $structure,
            'pages' => $pages,
            'hasRoot' => $tree->parent() !== null,
            'hasCollection' => $structure->collection() !== null,
            'collections' => $structure->collections()->map->handle()->all(),
            'localizations' => $structure->sites()->map(function ($handle) use ($structure, $tree) {
                $localized = $structure->in($handle);
                $exists = $localized !== null;
                if (!$exists) {
                    return null;
                }
                return [
                    'handle' => $handle,
                    'name' => Site::get($handle)->name(),
                    'active' => $handle === $tree->locale(),
                    'exists' => $exists,
                    'url' => $exists ? $localized->editUrl() : null,
                ];
            })->filter()->all()
        ]);
    }

    public function update(Request $request, $structure)
    {
        $validation = (new Validation)->fields(
            $fields = $this->editFormBlueprint()->fields()->addValues($request->all())->process()
        );

        $request->validate($validation->rules());

        $structure = Structure::find($structure);

        $values = $fields->values();

        $structure
            ->title($values['title'])
            ->handle($values['handle'])
            ->sites(collect($values['sites'])->filter->enabled->map->handle->values()->all());

        foreach ($values['sites'] as $site) {
            $tree = $structure->in($site['handle']);

            if ($tree && !$site['enabled']) {
                $structure->removeTree($tree);
            }

            if (!$tree && $site['enabled']) {
                $tree = $structure->makeTree($site['handle']);
            }

            if (!$site['enabled']) {
                continue;
            }

            $tree->route($site['route']);
            $structure->addTree($tree);
        }

        $structure->save();

        return [
            'title' => $structure->title(),
        ];
    }

    public function create()
    {
        return view('statamic::structures.create');
    }

    public function store(Request $request)
    {
        $values = $request->validate([
            'title' => 'required',
            'handle' => 'required|alpha_dash',
            'collections' => 'array',
            'collection' => 'nullable',
            'max_depth' => 'nullable|integer',
            'route' => 'nullable', // todo: change to the structuresites fieldtype
        ]);

        $structure = Structure::make()
            ->title($values['title'])
            ->handle($values['handle'])
            ->collections($values['collections'] ?? [])
            ->maxDepth($values['max_depth']);

        $sites = [ // todo: change to the structuresites fieldtype
            ['handle' => 'english', 'route' => $values['route']],
        ];

        foreach ($sites as $site) {
            $tree = $structure->makeTree($site['handle']);
            $tree->route($site['route']);
            $structure->addTree($tree);
        }

        $structure->save();

        if ($values['collection']) {
            Collection::findByHandle($values['collection'])->structure($structure->handle())->save();
            // todo: add all the collection's entries to the tree.
        }

        return ['redirect' => $structure->showUrl()];
    }

    public function editFormBlueprint()
    {
        return Blueprint::makeFromFields([
            'title' => [
                'type' => 'text',
                'validate' => 'required',
                'width' => 50,
            ],
            'handle' => [
                'type' => 'slug',
                'width' => 50,
                'read_only' => true,
            ],
            'sites' => [
                'type' => 'structure_sites',
                'validate' => 'required',
                'display' => Site::hasMultiple() ? __('Sites') : __('Route'),
            ]
        ]);
    }

    public function destroy($structure)
    {
        $structure = Structure::findByHandle($structure);

        $this->authorize('delete', $structure, 'You are not authorized to delete this structure.');

        return Structure::delete($structure);
    }
}

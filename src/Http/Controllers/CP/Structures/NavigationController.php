<?php

namespace Statamic\Http\Controllers\CP\Structures;

use Statamic\Support\Arr;
use Statamic\Facades\Site;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Structure;
use Illuminate\Http\Request;
use Statamic\Contracts\Structures\Nav as NavContract;
use Statamic\Facades\Collection;
use Statamic\Facades\User;
use Statamic\Structures\TreeBuilder;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Contracts\Structures\Structure as StructureContract;
use Statamic\Facades\Nav;

class NavigationController extends CpController
{
    public function index()
    {
        $this->authorize('index', NavContract::class, __('You are not authorized to view any navs.'));

        $navs = Nav::all()->filter(function ($nav) {
            return User::current()->can('view', $nav);
        })->map(function ($structure) {
            return [
                'id' => $structure->handle(),
                'title' => $structure->title(),
                'show_url' => $structure->showUrl(),
                'edit_url' => $structure->editUrl(),
                'delete_url' => $structure->deleteUrl(),
                'deleteable' => User::current()->can('delete', $structure)
            ];
        })->values();

        return view('statamic::navigation.index', compact('navs'));
    }

    public function edit($nav)
    {
        $nav = Nav::find($nav);

        $this->authorize('edit', $nav, __('You are not authorized to configure navs.'));

        $values = [
            'title' => $nav->title(),
            'handle' => $nav->handle(),
            'collections' => $nav->collections()->map->handle()->all(),
            'root' => $nav->expectsRoot(),
            'sites' => $nav->trees()->keys()->all(),
        ];

        $fields = ($blueprint = $this->editFormBlueprint($nav))
            ->fields()
            ->addValues($values)
            ->preProcess();

        return view('statamic::navigation.edit', [
            'blueprint' => $blueprint->toPublishArray(),
            'values' => $fields->values(),
            'meta' => $fields->meta(),
            'nav' => $nav,
        ]);
    }

    public function show(Request $request, $nav)
    {
        abort_if(! $nav = Nav::find($nav), 404);

        $site = $request->site ?? Site::selected()->handle();

        if (! $nav->existsIn($site)) {
            return redirect($nav->trees()->first()->showUrl());
        }

        return view('statamic::navigation.show', [
            'site' => $site,
            'nav' => $nav,
            'expectsRoot' => $nav->expectsRoot(),
            'collections' => $nav->collections()->map->handle()->all(),
            'sites' => $nav->trees()->map(function ($tree) {
                return [
                    'handle' => $tree->locale(),
                    'name' => $tree->site()->name(),
                    'url' => $tree->showUrl(),
                ];
            })->values()->all()
        ]);
    }

    public function update(Request $request, $nav)
    {
        $nav = Nav::find($nav);

        $this->authorize('update', $nav, __('You are not authorized to configure navs.'));

        $fields = $this->editFormBlueprint()->fields()->addValues($request->all());

        $fields->validate();

        $values = $fields->process()->values()->all();

        $nav
            ->title($values['title'])
            ->expectsRoot($values['root'])
            ->collections($values['collections'])
            ->maxDepth($values['max_depth']);

        $existingSites = $nav->trees()->keys()->all();

        if ($sites = Arr::get($values, 'sites')) {
            foreach ($sites as $site) {
                $tree = $nav->in($site) ?? $nav->makeTree($site);
                $nav->addTree($tree);
            }

            foreach (array_diff($existingSites, $sites) as $site) {
                $nav->removeTree($nav->in($site));
            }
        }

        $nav->save();

        return [
            'title' => $nav->title(),
        ];
    }

    public function create()
    {
        $this->authorize('create', NavContract::class, __('You are not authorized to configure navs.'));

        return view('statamic::navigation.create');
    }

    public function store(Request $request)
    {
        $this->authorize('store', NavContract::class, __('You are not authorized to configure navs.'));

        $values = $request->validate([
            'title' => 'required',
            'handle' => 'required|alpha_dash',
        ]);

        $structure = Nav::make()
            ->title($values['title'])
            ->handle($values['handle']);

        $structure->addTree($structure->makeTree(Site::default()->handle()));

        $structure->save();

        return ['redirect' => $structure->showUrl()];
    }

    public function editFormBlueprint()
    {
        $contents = [
            'name' => [
                'display' => 'Name',
                'fields' => [
                    'title' => [
                        'type' => 'text',
                        'validate' => 'required',
                        'instructions' => __('statamic::messages.navigation_configure_title_instructions'),
                    ],
                    'handle' => [
                        'type' => 'slug',
                        'instructions' => __('statamic::messages.navigation_configure_handle_instructions'),
                    ],
                ],
            ],
            'options' => [
                'display' => 'Options',
                'fields' => [
                    'collections' => [
                        'type' => 'collections',
                        'display' => 'Collections',
                        'instructions' => __('statamic::messages.navigation_configure_collections_instructions'),
                        'mode' => 'select',
                    ],
                    'root' => [
                        'type' => 'toggle',
                        'display' => 'Has a root page',
                        'instructions' => __('statamic::messages.navigation_configure_expect_root_instructions'),
                    ],
                    'max_depth' => [
                        'type' => 'integer',
                        'display' => 'Max depth',
                        'instructions' => __('statamic::messages.navigation_configure_max_depth_instructions'),
                        'validate' => 'min:0',
                    ],
                ],
            ],
        ];

        if (Site::hasMultiple()) {
            $contents['options']['fields']['sites'] = [
                'type' => 'sites',
                'display' => __('Sites'),
                'mode' => 'select',
                'required' => true,
            ];
        };

        return Blueprint::makeFromSections($contents);
    }

    public function destroy($nav)
    {
        $nav = Nav::findByHandle($nav);

        $this->authorize('delete', $nav, __('You are not authorized to configure navs.'));

        $nav->delete();
    }
}

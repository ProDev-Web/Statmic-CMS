<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Statamic\API\Str;
use Statamic\API\Site;
use Statamic\API\User;
use Statamic\CP\Column;
use Statamic\API\Action;
use Statamic\API\Filter;
use Statamic\API\Helper;
use Illuminate\Http\Request;
use Statamic\API\Collection;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Contracts\Data\Entries\Collection as CollectionContract;

class CollectionsController extends CpController
{
    public function index()
    {
        $this->authorize('index', CollectionContract::class, 'You are not authorized to view any collections.');

        $collections = Collection::all()->filter(function ($collection) {
            return request()->user()->can('view', $collection);
        })->map(function ($collection) {
            return [
                'id' => $collection->handle(),
                'title' => $collection->title(),
                'entries' => \Statamic\API\Entry::query()->where('collection', $collection->handle())->count(),
                'edit_url' => $collection->editUrl(),
                'entries_url' => cp_route('collections.show', $collection->handle())
            ];
        })->values();

        return view('statamic::collections.index', [
            'collections' => $collections,
            'columns' => [
                Column::make('title'),
                Column::make('entries'),
            ],
        ]);
    }

    public function show($collection)
    {
        $blueprints = $collection->entryBlueprints()->map(function ($blueprint) {
            return [
                'handle' => $blueprint->handle(),
                'title' => $blueprint->title(),
            ];
        });

        return view('statamic::collections.show', [
            'collection' => $collection,
            'blueprints' => $blueprints,
            'site' => Site::selected(),
            'filters' => Filter::for('entries', $context = [
                'collection' => $collection->handle(),
                'blueprints' => $blueprints->pluck('handle')->all(),
            ]),
            'actions' => Action::for('entries', $context),
        ]);
    }

    public function create()
    {
        $this->authorize('create', CollectionContract::class, 'You are not authorized to create collections.');

        return view('statamic::collections.create');
    }

    public function edit($collection)
    {
        $this->authorize('edit', $collection, 'You are not authorized to edit collections.');

        return view('statamic::collections.edit', compact('collection'));
    }

    public function store(Request $request)
    {
        $this->authorize('store', CollectionContract::class, 'You are not authorized to create collections.');

        $data = $request->validate([
            'title' => 'required',
            'handle' => 'nullable|alpha_dash',
            'template' => 'nullable',
            'fieldset' => 'nullable',
            'route' => 'nullable',
            'order' => 'nullable',
        ]);

        $handle = $request->handle ?? snake_case($request->title);

        $collection = tap(Collection::create($handle))
            ->data(array_except($data, 'handle'))
            ->save();

        session()->flash('message', __('Collection created'));

        return [
            'redirect' => $collection->showUrl()
        ];
    }

    public function update(Request $request, $collection)
    {
        $this->authorize('update', $collection, 'You are not authorized to edit collections.');

        $data = $request->validate([
            'title' => 'required',
            'template' => 'nullable',
            'fieldset' => 'nullable',
            'route' => 'nullable',
        ]);

        tap($collection)
            ->data(array_merge($collection->data(), $data))
            ->save();

        return redirect($collection->editUrl())
            ->with('success', 'Collection updated');
    }

    public function destroy($collection)
    {
        $this->authorize('delete', $collection, 'You are not authorized to delete collections.');

        $collection->delete();

        return redirect()
            ->route('statamic.cp.collections.index')
            ->with('success', 'Collection deleted.');
    }
}

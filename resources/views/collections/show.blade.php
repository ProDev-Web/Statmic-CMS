@extends('statamic::layout')
@section('title', Statamic::crumb($collection->title(), 'Collections'))
@section('wrapper_class', 'max-w-full')

@section('content')
    <header class="mb-3">
        @include('statamic::partials.breadcrumb', [
            'url' => cp_route('collections.index'),
            'title' => __('Collections')
        ])
        <div class="flex items-center">
            <h1 class="flex-1">{{ $collection->title() }}</h1>
            <dropdown-list class="mr-1">
                @can('edit', $collection)
                    <dropdown-item :text="__('Edit Collection')" redirect="{{ $collection->editUrl() }}"></dropdown-item>
                @endcan
                @can('edit', $collection)
                    <dropdown-item :text="__('Scaffold Resources')" redirect="{{ cp_route('collections.scaffold', $collection->handle()) }}"></dropdown-item>
                @endcan
                @can('delete', $collection)
                    <dropdown-item :text="__('Delete Collection')" class="warning" @click="$refs.deleter.confirm()">
                        <resource-deleter
                            ref="deleter"
                            resource-title="{{ $collection->title() }}"
                            route="{{ cp_route('collections.destroy', $collection->handle()) }}"
                            redirect="{{ cp_route('collections.index') }}"
                        ></resource-deleter>
                    </dropdown-item>
                @endcan
            </dropdown-list>
            @can('create', ['Statamic\Contracts\Entries\Entry', $collection])
                <create-entry-button
                    button-class="btn-primary"
                    url="{{ cp_route('collections.entries.create', [$collection->handle(), $site->handle()]) }}"
                    :blueprints="{{ $blueprints->toJson() }}">
                </create-entry-button>
            @endcan
        </div>
    </header>

    <entry-list
        collection="{{ $collection->handle() }}"
        initial-sort-column="{{ $collection->sortField() }}"
        initial-sort-direction="{{ $collection->sortDirection() }}"
        :filters="{{ $filters->toJson() }}"
        action-url="{{ cp_route('collections.entries.actions', $collection->handle()) }}"
        :reorderable="{{ Statamic\Support\Str::bool($collection->orderable() && $user->can('reorder', $collection)) }}"
        reorder-url="{{ cp_route('collections.entries.reorder', $collection->handle()) }}"
        structure-url="{{ optional($collection->structure())->showUrl() }}"
    ></entry-list>

@endsection

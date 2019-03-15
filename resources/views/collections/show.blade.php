@extends('statamic::layout')

@section('content')

    @if($collection->queryEntries()->count())

        <div class="flex items-center mb-3">
            <h1 class="flex-1">
                <small class="subhead block">
                    <a href="{{ cp_route('collections.index')}}">{{ __('Collections') }}</a>
                </small>
                {{ $collection->title() }}
            </h1>
            <create-entry-button
                url="{{ cp_route('collections.entries.create', [$collection->handle(), $site->handle()]) }}"
                :blueprints="{{ $blueprints->toJson() }}">
            </create-entry-button>
        </div>

        <entry-list
            collection="{{ $collection->handle() }}"
            initial-sort-column="title"
            initial-sort-direction="asc"
            :filters="{{ $filters->toJson() }}"
            :actions="{{ $actions->toJson() }}"
            action-url="{{ cp_route('collections.entries.action', $collection->handle()) }}"
        ></entry-list>

    @else

        @component('statamic::partials.create-first', [
            'resource' => __("{$collection->title()} Entry"),
            'svg' => 'empty/collection', // TODO: Do we want separate entry SVG?
        ])
            @slot('button')
                <create-entry-button
                    url="{{ cp_route('collections.entries.create', [$collection->handle(), $site->handle()]) }}"
                    :blueprints="{{ $blueprints->toJson() }}">
                </create-entry-button>
            @endslot
        @endcomponent

    @endif

@endsection

@extends('statamic::layout')

@section('content')

    @unless($structures->isEmpty())

        <div class="flex mb-3">
            <h1 class="flex-1">{{ __('Structures') }}</h1>

            @can('create', 'Statamic\Contracts\Data\Structures\Structure')
                <a href="{{ cp_route('collections.create') }}" class="btn-primary">{{ __('Create Structure') }}</a>
            @endcan
        </div>

        <structure-listing
            :initial-rows="{{ json_encode($structures) }}">
        </structure-listing>

    @else

        @include('statamic::partials.create-first', [
            'resource' => 'Structure',
            'description' => 'Structures are heirarchial arrangements of your content, most often used to represent forms of site navigation.',
            'svg' => 'empty/structure',
            'route' => cp_route('structures.create')
        ])

    @endunless

@endsection

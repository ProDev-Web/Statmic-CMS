@extends('statamic::layout')

@section('content')

    <div class="flex mb-3">
        <h1 class="flex-1">
            <a href="{{ cp_route('forms.index')}}">{{ __('Forms') }}</a>
            @svg('chevron-right')
            {{ $form->title() }}
        </h1>

        <a class="btn" href="{{ cp_route('forms.edit', $form->handle()) }}">{{ __('Edit') }}</a>
        <a class="btn ml-1" href="{{ cp_route('forms.export', ['type' => 'csv', 'form' => $form->handle()]) }}?download=true">{{ __('Export CSV') }}</a>
        <a class="btn ml-1" href="{{ cp_route('forms.export', ['type' => 'json', 'form' => $form->handle()]) }}?download=true">{{ __('Export JSON') }}</a>
    </div>

    @if (! empty($form->metrics()))
    <div class="metrics mb-3">
        @foreach($form->metrics() as $metric)
            <div class="card metric m-0 simple">
                <div class="count">
                    <small>{{ $metric->label() }}</small>
                    <h2>{{ $metric->result() }}</h2>
                </div>
            </div>
        @endforeach
    </div>
    @endif

    <form-submission-listing
        form="{{ $form->handle() }}">
    </form-submission-listing>

@endsection

@extends('statamic::layout')

@section('content')

    <asset-container-create-form
        initial-title="{{ __('Create Asset Container') }}"
        :blueprint="{{ json_encode($blueprint) }}"
        :initial-values="{{ json_encode($values) }}"
        :meta="{{ json_encode($meta) }}"
        url="{{ cp_route('asset-containers.store') }}"
        action="post"
    ></asset-container-create-form>

@endsection

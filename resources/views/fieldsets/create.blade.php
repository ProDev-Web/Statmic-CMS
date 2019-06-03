@extends('statamic::layout')
@section('title', __('Create Fieldset'))

@section('content')

    <fieldset-create-form
        action="{{ cp_route('fieldsets.store') }}"
        :initial-fieldset="{{ json_encode([
            'title' => '',
            'fields' => []
        ]) }}"
    ></fieldset-create-form>

@endsection

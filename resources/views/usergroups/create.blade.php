@extends('statamic::layout')
@section('title', __('Create User Group'))

@section('content')

    <user-group-publish-form
        action="{{ cp_route('user-groups.store') }}"
        method="post"
        breadcrumb-url="{{ cp_route('user-groups.index') }}"
        v-cloak
    ></user-group-publish-form>

@endsection

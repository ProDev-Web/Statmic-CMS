@extends('statamic::layout')
@section('title', __('Edit Role'))

@section('content')

    <role-publish-form
        action="{{ cp_route('roles.update', $role->handle()) }}"
        method="patch"
        initial-title="{{ $role->title() }}"
        initial-handle="{{ $role->handle() }}"
        :initial-super="{{ Statamic\Support\Str::bool($super) }}"
        :initial-permissions="{{ json_encode($permissions) }}"
        v-cloak
    >

        <h1 class="flex-1" slot="heading" slot-scope="{ title }">
            <a href="{{ cp_route('roles.index') }}">{{ __('Roles & Permissions') }}</a>
            @svg('chevron-right')
            @{{ title }}
        </h1>

    </role-publish-form>

@endsection

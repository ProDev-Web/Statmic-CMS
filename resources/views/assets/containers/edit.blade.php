@extends('statamic::layout')

@section('content')

    <form method="POST" action="{{ cp_route('asset-containers.update', $container->handle()) }}">
        @method('patch') @csrf

        <div class="flex items-center mb-3">
            <h1 class="flex-1">{{ $container->title() }}</h1>
            <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
        </div>

        <div class="publish-form p-0 card">

            <div class="form-group">
                <label class="block">{{ __('Title') }}</label>
                <small class="help-block">{{ __('The proper name of your container.') }}</small>
                <input type="text" name="title" class="form-control" value="{{ old('title', $container->title()) }}" autofocus="autofocus">
            </div>

            <div class="form-group">
                <label class="block">{{ __('Disk') }}</label>
                <small class="help-block">{{ __('The filesystem disk this container will use.') }}</small>
                <select-input
                    name="disk"
                    value="{{ old('disk', array_get($container->data(), 'disk')) }}"
                    :options="{{ json_encode($disks) }}"
                ></select-input>
            </div>

            <div class="form-group">
                <label class="block">{{ __('Path') }}</label>
                <small class="help-block">{{ __('The path to the root directory within the selected filesystem disk. Leave blank to use the root directory.') }}</small>
                <input type="text" name="path" class="form-control" value="{{ old('path', $container->path()) }}">
            </div>

            <div class="form-group">
                <label class="block">{{ __('Fieldset') }}</label>
                <small class="help-block">{{ __('The default fieldset, unless otherwise specified.') }}</small>
                {{-- TODO: Bring back fieldset fieldtype. --}}
                <input type="text" name="fieldset" class="form-control" value="{{ old('fieldset', array_get($container->data(), 'fieldset')) }}">
            </div>

        </div>
    </form>

@endsection

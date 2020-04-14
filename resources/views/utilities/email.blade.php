@extends('statamic::layout')
@section('title', Statamic::crumb(__('Email'), __('Utilities')))

@section('content')

    <header class="mb-3">
        @include('statamic::partials.breadcrumb', [
            'url' => cp_route('utilities.index'),
            'title' => __('Utilities')
        ])
        <h1>{{ __('Email') }}</h1>
    </header>

    <div class="card">
        <form method="POST" action="{{ cp_route('utilities.email') }}">
            @csrf

            <div class="flex items-center">
                <input class="input-text mr-2" type="text" name="email" value="{{ old('email', $user->email()) }}" />
                <button type="submit" class="btn-primary">{{ __('Send Test Email') }}</button>
            </div>
            @if ($errors->has('email'))
                <p class="mt-1"><small class="help-block text-red">{{ $errors->first('email') }}</small></p>
            @endif
        </form>
    </div>

    <h2 class="mt-5 mb-1 font-bold text-lg">{{ __('Configuration') }}</h2>
    <p class="text-sm text-grey mb-2">{!! __('statamic::messages.email_utility_configuration_description', ['path' => config_path('mail.php')]) !!}</p>
    <div class="card p-0">
        <table class="data-table">
            @if (config('mail.mailers'))
                @include('statamic::utilities.partials.email-l7')
            @else
                @include('statamic::utilities.partials.email-l6')
            @endif
            <tr>
                <th class="pl-2 py-1 w-1/4">{{ __('Default From Address') }}</th>
                <td><code>{{ config('mail.from.address') }}</code></td>
            </tr>
            <tr>
                <th class="pl-2 py-1 w-1/4">{{ __('Default From Name') }}</th>
                <td><code>{{ config('mail.from.name') }}</code></td>
            </tr>
            <tr>
                <th class="pl-2 py-1 w-1/4">{{ __('Markdown theme') }}</th>
                <td><code>{{ config('mail.markdown.theme') }}</code></td>
            </tr>
            <tr>
                <th class="pl-2 py-1 w-1/4">{{ __('Markdown paths') }}</th>
                <td>
                    @foreach (config('mail.markdown.paths') as $path)
                        <code>{{ $path }}</code><br>
                    @endforeach
                </td>
            </tr>
        </table>
    </div>

@stop

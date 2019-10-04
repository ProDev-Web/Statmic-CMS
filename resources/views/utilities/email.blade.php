@extends('statamic::layout')
@section('title', Statamic::crumb(__('Email'), __('Utilities')))

@section('content')

    <h1>
        <small class="subhead block">
            <a href="{{ cp_route('utilities.index')}}">{{ __('Utilities') }}</a>
        </small>
        {{ __('Email') }}
    </h1>

    <div class="mt-4 p-3 rounded shadow bg-white">
        <form method="POST" action="{{ cp_route('utilities.email') }}">
            @csrf

            <div class="flex items-center">
                <input class="input-text mr-2" type="text" name="email" value="{{ old('email', $user->email()) }}" />
                <button type="submit" class="btn btn-primary">{{ __('Send Test Email') }}</button>
            </div>
            @if ($errors->has('email'))
                <p class="mt-1"><small class="help-block text-red">{{ $errors->first('email') }}</small></p>
            @endif
        </form>
    </div>

    <h2 class="mt-4 mb-1 font-bold text-xl">Configuration</h2>
    <p class="text-sm text-grey mb-2">Mail settings are configured in <code>{{ config_path('mail') }}</code></p>
    <div class="card p-0">
        <table class="data-table">
            <tr>
                <th class="pl-2 py-1 w-1/4">Driver</th>
                <td><code>{{ config('mail.driver') }}</code></td>
            </tr>
            <tr>
                <th class="pl-2 py-1 w-1/4">Host</th>
                <td><code>{{ config('mail.host') }}</code></td>
            </tr>
            <tr>
                <th class="pl-2 py-1 w-1/4">Port</th>
                <td><code>{{ config('mail.port') }}</code></td>
            </tr>
            <tr>
                <th class="pl-2 py-1 w-1/4">Default From Address</th>
                <td><code>{{ config('mail.from.address') }}</code></td>
            </tr>
            <tr>
                <th class="pl-2 py-1 w-1/4">Default From Name</th>
                <td><code>{{ config('mail.from.name') }}</code></td>
            </tr>
            <tr>
                <th class="pl-2 py-1 w-1/4">Encryption</th>
                <td><code>{{ config('mail.encryption') }}</code></td>
            </tr>
            <tr>
                <th class="pl-2 py-1 w-1/4">Username</th>
                <td><code>{{ config('mail.username') }}</code></td>
            </tr>
            <tr>
                <th class="pl-2 py-1 w-1/4">Password</th>
                <td><code>{{ config('mail.password') }}</code></td>
            </tr>
            <tr>
                <th class="pl-2 py-1 w-1/4">Sendmail</th>
                <td><code>{{ config('mail.sendmail') }}</code></td>
            </tr>
            <tr>
                <th class="pl-2 py-1 w-1/4">Markdown theme</th>
                <td><code>{{ config('mail.markdown.theme') }}</code></td>
            </tr>
            <tr>
                <th class="pl-2 py-1 w-1/4">Markdown paths</th>
                <td>
                    @foreach (config('mail.markdown.paths') as $path)
                        <code>{{ $path }}</code><br>
                    @endforeach
                </td>
            </tr>
        </table>
    </div>

@stop

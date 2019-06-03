@extends('statamic::outside')
@section('body_class', 'rad-mode')
@section('title', __('Login'))

@section('content')
<div class="logo pt-7">
    {!! inline_svg('statamic-wordmark') !!}
</div>

<div class="card auth-card mx-auto">
    <login inline-template :show-email-login="!{{ bool_str($oauth) }}" :has-error="{{ bool_str(count($errors) > 0) }}">

        @if ($oauth)
            <div class="login-oauth-providers">
                @foreach (Statamic\API\OAuth::providers() as $provider => $data)
                    <div class="provider">
                        <a href="{{ Statamic\API\OAuth::route($provider) }}?redirect={{ parse_url(route('cp'))['path'] }}" class="btn btn-block btn-primary">
                            {{ t('login_with', ['provider' => array_get($data, 'label', \Statamic\API\Str::title($provider))]) }}
                        </a>
                    </div>
                @endforeach
            </div>

            <div class="text-center italic mx-1">or</div>

            <div class="login-with-email" v-if="! showEmailLogin">
                <a class="btn btn-block" @click.prevent="showEmailLogin = true">
                    {{ t('login_with', ['provider' => t(\Statamic\API\Config::get('users.login_type'))]) }}
                </a>
            </div>
        @endif

        <form method="POST" v-show="showEmailLogin" class="email-login select-none">
            {!! csrf_field() !!}

            <input type="hidden" name="referer" value="{{ $referer }}" />

            <div class="mb-4">
                <label class="mb-1">{{ __('Email') }}</label>
                <input type="text" class="input-text input-text" name="email" value="{{ old('email') }}" autofocus>
            </div>

            <div class="mb-4">
                <label class="mb-1">{{ __('Password') }}</label>
                <input type="password" class="input-text input-text" name="password" id="password">
            </div>
            <div class="flex justify-between items-center">
                <label for="remember_me" class="flex items-center cursor-pointer">
                    <input type="checkbox" name="remember" id="remember_me">
                    <span class="ml-1">{{ __('Remember me') }}</span>
                </label>
                <button type="submit" class="btn btn-primary">{{ __('Login') }}</button>
            </div>
        </form>
    </login>
</div>
@if (! $oauth)
    <div class="w-full text-center mt-2">
        <a href="{{ cp_route('password.request')}}" class="forgot-password-link text-sm opacity-75 hover:opacity-100">
            {{ __('Forgot password?') }}
        </a>
    </div>
@endif

@endsection

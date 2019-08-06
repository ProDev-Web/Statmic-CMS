@extends('statamic::outside')
@section('body_class', 'rad-mode')
@section('title', __('Login'))

@section('content')
<div class="logo pt-7">
    {!! inline_svg('statamic-wordmark') !!}
</div>

<div class="card auth-card mx-auto">
    <login inline-template :show-email-login="!{{ bool_str($oauth) }}" :has-error="{{ bool_str(count($errors) > 0) }}">
    <div>
        @if ($oauth)
            <div class="login-oauth-providers">
                @foreach ($providers as $provider)
                    <div class="provider">
                        <a href="{{ $provider->redirectUrl() }}?redirect={{ parse_url(cp_route('index'))['path'] }}" class="btn block btn-primary">
                            {{ __('Login with :provider', ['provider' => $provider->label()]) }}
                        </a>
                    </div>
                @endforeach
            </div>

            <div class="text-center italic my-3">or</div>

            <div class="login-with-email" v-if="! showEmailLogin">
                <a class="btn block" @click.prevent="showEmailLogin = true">
                    {{ __('Login with email') }}
                </a>
            </div>
        @endif

        <form method="POST" v-show="showEmailLogin" class="email-login select-none" v-cloak>
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
    </div>
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

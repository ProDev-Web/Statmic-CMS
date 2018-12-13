@extends('statamic::outside')
@section('body_class', 'rad-mode')

@section('content')

    <h1 class="mb-3 pt-7 text-center text-grey-dark">{{ __('Reset Password') }}</h1>

    <div class="card auth-card mx-auto">

        <form method="POST" action="{{ cp_route('password.request') }}">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">

            <div class="mb-4">
                <label for="email"  class="mb-1">{{ __('Email Address') }}</label>

                @if ($errors->has('email'))
                    <small class="block text-red -mt-1 mb-1">{{ $errors->first('email') }}</small>
                @endif

                <input id="email" type="email" class="input-text form-control" name="email" value="{{ $email ?? old('email') }}" autofocus required>
            </div>

            <div class="mb-4">
                <label for="password" class="mb-1">{{ __('Password') }}</label>

                @if ($errors->has('password'))
                    <small class="block text-red -mt-1 mb-1">{{ $errors->first('password') }}</small>
                @endif

                <input id="password" type="password" class="input-text form-control" name="password" required>
            </div>

            <div class="mb-4">
                <label for="password-confirm" class="mb-1">{{ __('Confirm Password') }}</label>

                <input id="password-confirm" type="password" class="input-text form-control" name="password_confirmation" required>
            </div>

            <button type="submit" class="btn btn-primary">
                {{ __('Reset Password') }}
            </button>

        </form>

    </div>

@endsection

<?php

namespace Statamic\Http\Controllers\CP\Auth;

use Statamic\API\Str;
use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Statamic\Http\Middleware\CP\RedirectIfAuthenticated;

class LoginController extends CpController
{
    use AuthenticatesUsers;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(RedirectIfAuthenticated::class)->except('logout');
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm(Request $request)
    {
        $data = [
            'title' => __('Login'),
            // 'oauth' => OAuth::enabled() && !empty(OAuth::providers()),
            'oauth' => false,
            'referer' => url()->previous()
        ];

        $view = view('statamic::auth.login', $data);

        if ($request->expired) {
            return $view->withErrors(t('session_expired'));
        }

        return $view;
    }

    public function redirectPath()
    {
        $referer = request('referer');

        return Str::contains($referer, '/'.config('statamic.cp.route')) ? $referer : cp_route('index');
    }

    protected function authenticated(Request $request, $user)
    {
        return $request->expectsJson()
            ? response('Authenticated')
            : redirect()->intended($this->redirectPath());
    }
}

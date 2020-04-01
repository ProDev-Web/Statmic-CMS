<?php

namespace Statamic\Exceptions;

use Exception;
use Statamic\Statamic;
use App\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\Access\AuthorizationException as IlluminateAuthException;

class Handler extends ExceptionHandler
{
    public function render($request, Exception $e)
    {
        if ($e instanceof NotFoundHttpException && Statamic::isApiRoute()) {
            return response()->json(['message' => $e->getMessage() ?: 'Not found.'], 404);
        }

        if ($e instanceof IlluminateAuthException && Statamic::isCpRoute() && !$request->expectsJson()) {
            return redirect($this->getAuthExceptionRedirectUrl())->withError($e->getMessage());
        }

        return parent::render($request, $e);
    }

    protected function getAuthExceptionRedirectUrl()
    {
        $referrer = request()->header('referer');

        // If we came to this URL from another, we'll send them back, but not
        // if it was the login page otherwise there'd be a redirect loop.
        if ($referrer && $referrer != cp_route('login')) {
            return $referrer;
        }

        // If we can't send them back because they hit this page directly,
        // we'll attempt to redirect them to the Control Panel index.
        $target = cp_route('index');

        // If we're already there, there'd be a redirect loop, so we'll
        // send them to a page that tells them they're unauthorized.
        if (request()->getUri() === $target) {
            return cp_route('unauthorized');
        }

        return $target;
    }
}

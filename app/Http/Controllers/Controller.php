<?php

namespace Statamic\Http\Controllers;

use Statamic\Facades\User;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Set the successful flash message
     *
     * @param string $message
     * @param null   $text
     * @return array
     */
    protected function success($message, $text = null)
    {
        session()->flash('success', $message);

        if ($text) {
            session()->flash('success_text', $text);
        }
    }
}

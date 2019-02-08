<?php

namespace Statamic\Tags;

use Statamic\Tags\Tags;
use Statamic\Exceptions\UrlNotFoundException;

class NotFound extends Tags
{
    protected static $aliases = ['404'];

    public function index()
    {
        throw new UrlNotFoundException;
    }
}

<?php

namespace Statamic\Events;

class GlobalSetSaved extends Saved
{
    public $globals;

    public function __construct($globals)
    {
        $this->globals = $globals;
    }

    public function commitMessage()
    {
        return __('Global Set saved');
    }
}

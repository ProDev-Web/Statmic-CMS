<?php

namespace Statamic\Events;

class NavDeleted extends Deleted
{
    public $item;

    public function __construct($item)
    {
        $this->item = $item;
    }

    public function commitMessage()
    {
        return __('Navigation deleted');
    }
}

<?php

namespace Statamic\Events;

class CollectionSaved extends Saved
{
    public $item;

    public function __construct($item)
    {
        $this->item = $item;
    }

    public function commitMessage()
    {
        return __('Collection saved');
    }
}

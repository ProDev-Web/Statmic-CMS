<?php

namespace Statamic\Stache\Stores;

class Store
{
    protected $directory;

    public function directory($directory = null)
    {
        if ($directory === null) {
            return $this->directory;
        }

        $this->directory = $directory;

        return $this;
    }
}

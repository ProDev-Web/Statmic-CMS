<?php

namespace Statamic\Data;

trait HasOrigin
{
    protected $origin;

    public function values()
    {
        $originValues = $this->origin ? $this->origin->values() : [];

        return array_merge($originValues, $this->data);
    }

    public function origin($origin = null)
    {
        return $this->fluentlyGetOrSet('origin')->args(func_get_args());
    }
}

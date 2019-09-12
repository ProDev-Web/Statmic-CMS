<?php

namespace Statamic\Tags;

use Statamic\Facades\Arr;
use Illuminate\Support\Collection;

class ArrayAccessor extends Collection
{
    public function get($key, $default = null)
    {
        return Arr::getFirst($this->items, Arr::wrap($key), $default);
    }

    public function explode($key, $default = null)
    {
        if (! $value = $this->get($key)) {
            return $default;
        }

        return explode('|', $value);
    }

    public function bool($key, $default = false)
    {
        if (! $value = $this->get($key)) {
            return $default;
        }

        return (bool) $value;
    }

    public function int($key, $default = 0)
    {
        if (! $value = $this->get($key)) {
            return $default;
        }

        return (int) $value;
    }

    public function float($key, $default = 0.0)
    {
        if (! $value = $this->get($key)) {
            return is_int($default) ? (float) $default : $default;
        }

        return (float) $value;
    }
}

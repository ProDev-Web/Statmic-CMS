<?php

namespace Statamic\Fields;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;

class LabeledValue implements Arrayable
{
    protected $value;
    protected $label;

    public function __construct($value, $label)
    {
        $this->value = $value;
        $this->label = $label;
    }

    public function value()
    {
        return $this->value;
    }

    public function label()
    {
        return $this->label;
    }

    public function __toString()
    {
        return $this->value;
    }

    public function toArray()
    {
        return [
            'key' => $this->value,
            'value' => $this->value,
            'label' => $this->label,
        ];
    }
}

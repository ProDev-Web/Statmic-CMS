<?php

namespace Statamic\CP;

use Statamic\Support\Str;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class Column
{
    use FluentlyGetsAndSets;

    public $field;
    public $fieldtype;
    public $label;
    public $listable = true;
    public $visibleDefault = true;
    public $visible = true;
    public $sortable = true;
    public $value = null;

    /**
     * Make new column instance.
     *
     * @param null|string $field
     * @return static
     */
    public static function make($field = null)
    {
        $column = new static;

        return $field
            ? $column->field($field)
            : $column;
    }

    /**
     * Get or set field.
     *
     * @param null|string $field
     * @return mixed
     */
    public function field($field = null)
    {
        return $this
            ->fluentlyGetOrSet('field')
            ->afterSetter(function ($field) {
                if (is_null($this->label)) {
                    $this->label(Str::slugToTitle($field), true);
                }
            })
            ->value($field);
    }

    /**
     * Get or set the value field.
     *
     * @param null|string $value
     * @return mixed
     */
    public function value($value = null)
    {
        return $this->fluentlyGetOrSet('value')->value($value);
    }

    /**
     * Get or set fieldtype.
     *
     * @param null|string $fieldtype
     * @return mixed
     */
    public function fieldtype($fieldtype = null)
    {
        return $this->fluentlyGetOrSet('fieldtype')->value($fieldtype);
    }

    /**
     * Get or set label.
     *
     * @param null|string $label
     * @return mixed
     */
    public function label($label = null)
    {
        return $this->fluentlyGetOrSet('label')->value($label);
    }

    /**
     * Get or set listable.  Setting `false` will override visibility.
     *
     * @param mixed $listable
     */
    public function listable($listable = null)
    {
        return $this->fluentlyGetOrSet('listable')->value($listable);
    }

    /**
     * Get or set visibility default, for resetting user preferences, etc.
     *
     * @param null|bool $visibleDefault
     * @return mixed
     */
    public function visibleDefault($visible = null)
    {
        return $this->fluentlyGetOrSet('visibleDefault')->value($visible);
    }

    /**
     * Get or set visibility.
     *
     * @param null|bool $visible
     * @return mixed
     */
    public function visible($visible = null)
    {
        return $this->fluentlyGetOrSet('visible')->value($visible);
    }

    /**
     * Get or set sortable.
     *
     * @param null|bool $sortable
     * @return mixed
     */
    public function sortable($sortable = null)
    {
        return $this->fluentlyGetOrSet('sortable')->value($sortable);
    }

    /**
     * Cast column to array.
     *
     * return array
     */
    public function toArray()
    {
        return (array) $this;
    }
}

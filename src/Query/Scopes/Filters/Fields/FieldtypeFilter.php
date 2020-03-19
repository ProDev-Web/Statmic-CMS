<?php

namespace Statamic\Query\Scopes\Filters\Fields;

use Statamic\Extend\HasFields;
use Statamic\Support\Arr;

class FieldtypeFilter
{
    use HasFields;

    protected $fieldtype;

    public function __construct($fieldtype)
    {
        $this->fieldtype = $fieldtype;
    }

    public function fieldItems()
    {
        return [
            'operator' => [
                'type' => 'select',
                'options' => [
                    '=' => __('Is'),
                    '<>' => __('Isn\'t'),
                    'like' => __('Contains'),
                ]
            ],
            'value' => [
                'type' => $this->fieldtype->field()->toPublishArray(),
            ],
        ];
    }

    public function apply($query, $handle, $values)
    {
        $operator = $values['operator'];
        $value = $values['value'];

        if ($operator === 'like') {
            $value = Str::ensureLeft($value, '%');
            $value = Str::ensureRight($value, '%');
        }

        $query->where($handle, $operator, $value);
    }

    public function badge($handle, $values)
    {
        $field = $this->fieldtype->field()->display();
        $operator = $values['operator'];
        $translatedOperator = Arr::get($this->fieldItems(), "operator.options.{$operator}");
        $value = $values['value'];

        return strtolower($field) . ' ' . strtolower($translatedOperator) . ' ' . $value;
    }
}

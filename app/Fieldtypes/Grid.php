<?php

namespace Statamic\Fieldtypes;

use Statamic\API\Helper;
use Statamic\Fields\Fields;
use Statamic\Fields\Fieldtype;
use Statamic\Fields\Validation;
use Statamic\CP\FieldtypeFactory;
use Statamic\Fields\ConfigFields;

class Grid extends Fieldtype
{
    protected $defaultable = false;

    protected $configFields = [
        'mode' => [
            'type' => 'select',
            'default' => 'table',
            'instructions' => 'Choose the layout style you wish to use by default.',
            'options' => [
                'table' => 'Table',
                'stacked' => 'Stacked'
            ],
        ],
        'max_rows' => [
            'type' => 'integer',
            'width' => '50',
            'instructions' => 'Set a maximum number of rows that can be created.',
        ],
        'min_rows' => [
            'type' => 'integer',
            'width' => '50',
            'instructions' => 'Set a minimum number of rows that must be created.',
        ],
        'add_row' => [
            'type' => 'text',
            'instructions' => 'Set the label of the "Add Row" button.',
        ],
        'reorderable' => [
            'type' => 'toggle',
            'default' => true,
            'instructions' => 'Enable to allow row reordering.',
        ],
        'fields' => [
            'type' => 'fields'
        ],
    ];

    public function process($data)
    {
        return collect($data)->map(function ($row) {
            return $this->processRow($row);
        })->all();
    }

    private function processRow($row)
    {
        $row = array_except($row, '_id');

        $fields = $this->fields()->addValues($row)->process()->values();

        return array_merge($row, $fields);
    }

    public function preProcess($data)
    {
        return collect($data)->map(function ($row, $i) {
            return $this->preProcessRow($row, $i);
        })->all();
    }

    private function preProcessRow($row, $index)
    {
        $fields = $this->fields()->addValues($row)->preProcess()->values();

        return array_merge($row, $fields, [
            '_id' => "row-$index",
        ]);
    }

    private function fields()
    {
        return new Fields($this->config('fields'));
    }

    public function rules(): array
    {
        $rules = ['array'];

        if ($min = $this->config('min_rows')) {
            $rules[] = 'min:' . $min;
        }

        if ($max = $this->config('max_rows')) {
            $rules[] = 'max:' . $max;
        }

        return $rules;
    }

    public function extraRules(): array
    {
        $rules = (new Validation)->fields($this->fields())->rules();

        return collect($rules)->mapWithKeys(function ($rules, $handle) {
            return ["{$this->field->handle()}.*.{$handle}" => $rules];
        })->all();
    }

    public function preload()
    {
        return [
            'defaults' => $this->defaultRowData(),
            'new' => $this->fields()->meta(),
            'existing' => collect($this->field->value())->mapWithKeys(function ($row) {
                return [$row['_id'] => $this->fields()->addValues($row)->meta()];
            })->toArray(),
        ];
    }

    protected function defaultRowData()
    {
        return $this->fields()->all()->map->defaultValue();
    }
}

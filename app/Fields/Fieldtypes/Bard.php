<?php

namespace Statamic\Fields\Fieldtypes;

use Statamic\Fields\Fieldtypes\Bard\Augmentor;

class Bard extends Replicator
{
    public $category = ['text', 'structured'];

    public function augment($value)
    {
        return (new Augmentor)->augment($value);
    }

    public function process($value)
    {
        $value = json_decode($value, true);

        return collect($value)->map(function ($row) {
            if ($row['type'] !== 'set') {
                return $row;
            }

            return $this->processRow($row);
        })->all();
    }

    protected function processRow($row)
    {
        $row['attrs']['values'] = parent::processRow($row['attrs']['values']);

        if ($row['attrs']['enabled'] === true) {
            unset($row['attrs']['enabled']);
        }

        return $row;
    }

    public function preProcess($value)
    {
        return collect($value)->map(function ($row) {
            if ($row['type'] !== 'set') {
                return $row;
            }

            return $this->preProcessRow($row);
        })->toJson();
    }

    protected function preProcessRow($row)
    {
        $processed = parent::preProcessRow($row['attrs']['values']);

        return [
            'type' => 'set',
            'attrs' => [
                'values' => $processed,
            ]
        ];
    }
}
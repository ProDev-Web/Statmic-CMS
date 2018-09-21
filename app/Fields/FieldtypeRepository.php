<?php

namespace Statamic\Fields;

use Exception;

class FieldtypeRepository
{
    public function find($handle)
    {
        if (! ($fieldtypes = app('statamic.fieldtypes'))->has($handle)) {
            throw new Exception("Fieldtype [$handle] does not exist.");
        }

        return app($fieldtypes->get($handle));
    }
}

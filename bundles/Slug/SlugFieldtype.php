<?php

namespace Statamic\Addons\Slug;

use Statamic\Fields\Fieldtype;
use Statamic\Addons\Text\TextFieldtype;

class SlugFieldtype extends TextFieldtype
{
    protected $configFields = [
        'generate' => ['type' => 'toggle', 'default' => true],
    ];
}

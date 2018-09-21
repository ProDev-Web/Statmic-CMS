<?php

namespace Tests\Fields\Fieldtypes;

use Tests\TestCase;
use Statamic\Fields\Field;
use Statamic\Fields\Fieldtype;
use Statamic\Fields\Fieldtypes\Grid;
use Statamic\Fields\Fieldtypes\NestedFields;
use Facades\Statamic\Fields\FieldtypeRepository;

class GridTest extends TestCase
{
    /** @test */
    function it_preprocesses_the_values()
    {
        $field = (new Field('test', [
            'type' => 'grid',
            'fields' => [
                'numbers' => ['type' => 'integer'],
                'words' => ['type' => 'text']
            ]
        ]))->setValue([
            [
                'numbers' => '2', // corresponding fieldtype has preprocessing
                'words' => 'test', // corresponding fieldtype has no preprocessing
                'foo' => 'bar' // no corresponding fieldtype, so theres no preprocessing
            ],
            [
                'numbers' => '3', // corresponding fieldtype has preprocessing
                'words' => 'more test', // corresponding fieldtype has no preprocessing
                'foo' => 'more bar' // no corresponding fieldtype, so theres no preprocessing
            ]
        ]);

        $this->assertSame([
            [
                'numbers' => 2,
                'words' => 'test',
                'foo' => 'bar',
            ],
            [
                'numbers' => 3,
                'words' => 'more test',
                'foo' => 'more bar',
            ]
        ], $field->preProcess()->value());
    }

    /** @test */
    function it_preprocesses_the_values_recursively()
    {
        $field = (new Field('test', [
            'type' => 'grid',
            'fields' => [
                'numbers' => ['type' => 'integer'],
                'words' => ['type' => 'text'],
                'nested_grid' => ['type' => 'grid', 'fields' => [
                    'nested_numbers' => ['type' => 'integer'],
                    'nested_words' => ['type' => 'text'],
                ]]
            ]
        ]))->setValue([
            [
                'numbers' => '2', // corresponding fieldtype has preprocessing
                'words' => 'test', // corresponding fieldtype has no preprocessing
                'foo' => 'bar', // no corresponding fieldtype, so theres no preprocessing
                'nested_grid' => [
                    [
                        'nested_numbers' => '3', // corresponding fieldtype has preprocessing
                        'nested_words' => 'nested test one', // corresponding fieldtype has no preprocessing
                        'nested_foo' => 'nested bar one', // no corresponding fieldtype, so theres no preprocessing
                    ],
                    [
                        'nested_numbers' => '4', // corresponding fieldtype has preprocessing
                        'nested_words' => 'nested test two', // corresponding fieldtype has no preprocessing
                        'nested_foo' => 'nested bar two', // no corresponding fieldtype, so theres no preprocessing
                    ]
                ]
            ],
            [
                'numbers' => '3', // corresponding fieldtype has preprocessing
                'words' => 'more test', // corresponding fieldtype has no preprocessing
                'foo' => 'more bar', // no corresponding fieldtype, so theres no preprocessing
                'nested_grid' => [
                    [
                        'nested_numbers' => '5', // corresponding fieldtype has preprocessing
                        'nested_words' => 'more nested test one', // corresponding fieldtype has no preprocessing
                        'nested_foo' => 'more nested bar one', // no corresponding fieldtype, so theres no preprocessing
                    ],
                    [
                        'nested_numbers' => '6', // corresponding fieldtype has preprocessing
                        'nested_words' => 'more nested test two', // corresponding fieldtype has no preprocessing
                        'nested_foo' => 'more nested bar two', // no corresponding fieldtype, so theres no preprocessing
                    ]
                ]
            ]
        ]);

        $this->assertSame([
            [
                'numbers' => 2,
                'words' => 'test',
                'foo' => 'bar',
                'nested_grid' => [
                    [
                        'nested_numbers' => 3,
                        'nested_words' => 'nested test one',
                        'nested_foo' => 'nested bar one',
                    ],
                    [
                        'nested_numbers' => 4,
                        'nested_words' => 'nested test two',
                        'nested_foo' => 'nested bar two',
                    ]
                ]
            ],
            [
                'numbers' => 3,
                'words' => 'more test',
                'foo' => 'more bar',
                'nested_grid' => [
                    [
                        'nested_numbers' => 5,
                        'nested_words' => 'more nested test one',
                        'nested_foo' => 'more nested bar one',
                    ],
                    [
                        'nested_numbers' => 6,
                        'nested_words' => 'more nested test two',
                        'nested_foo' => 'more nested bar two',
                    ]
                ]
            ]
        ], $field->preProcess()->value());
    }

    /** @test */
    function it_processes_the_values()
    {
        $field = (new Field('test', [
            'type' => 'grid',
            'fields' => [
                'numbers' => ['type' => 'integer'],
                'words' => ['type' => 'text']
            ]
        ]))->setValue([
            [
                '_id' => 'id-1', // comes from vue
                'numbers' => '2', // corresponding fieldtype has preprocessing
                'words' => 'test', // corresponding fieldtype has no preprocessing
                'foo' => 'bar' // no corresponding fieldtype, so theres no preprocessing
            ],
            [
                '_id' => 'id-2', // comes from vue
                'numbers' => '3', // corresponding fieldtype has preprocessing
                'words' => 'more test', // corresponding fieldtype has no preprocessing
                'foo' => 'more bar' // no corresponding fieldtype, so theres no preprocessing
            ]
        ]);

        $this->assertSame([
            [
                'numbers' => 2,
                'words' => 'test',
                'foo' => 'bar',
            ],
            [
                'numbers' => 3,
                'words' => 'more test',
                'foo' => 'more bar',
            ]
        ], $field->process()->value());
    }

    /** @test */
    function it_processes_the_values_recursively()
    {
        $field = (new Field('test', [
            'type' => 'grid',
            'fields' => [
                'numbers' => ['type' => 'integer'],
                'words' => ['type' => 'text'],
                'nested_grid' => ['type' => 'grid', 'fields' => [
                    'nested_numbers' => ['type' => 'integer'],
                    'nested_words' => ['type' => 'text'],
                ]]
            ]
        ]))->setValue([
            [
                '_id' => 'id-1', // comes from vue
                'numbers' => '2', // corresponding fieldtype has preprocessing
                'words' => 'test', // corresponding fieldtype has no preprocessing
                'foo' => 'bar', // no corresponding fieldtype, so theres no preprocessing
                'nested_grid' => [
                    [
                        '_id' => 'id-1-1', // comes from vue
                        'nested_numbers' => '3', // corresponding fieldtype has preprocessing
                        'nested_words' => 'nested test one', // corresponding fieldtype has no preprocessing
                        'nested_foo' => 'nested bar one', // no corresponding fieldtype, so theres no preprocessing
                    ],
                    [
                        '_id' => 'id-1-2', // comes from vue
                        'nested_numbers' => '4', // corresponding fieldtype has preprocessing
                        'nested_words' => 'nested test two', // corresponding fieldtype has no preprocessing
                        'nested_foo' => 'nested bar two', // no corresponding fieldtype, so theres no preprocessing
                    ]
                ]
            ],
            [
                '_id' => 'id-2', // comes from vue
                'numbers' => '3', // corresponding fieldtype has preprocessing
                'words' => 'more test', // corresponding fieldtype has no preprocessing
                'foo' => 'more bar', // no corresponding fieldtype, so theres no preprocessing
                'nested_grid' => [
                    [
                        '_id' => 'id-2-1', // comes from vue
                        'nested_numbers' => '5', // corresponding fieldtype has preprocessing
                        'nested_words' => 'more nested test one', // corresponding fieldtype has no preprocessing
                        'nested_foo' => 'more nested bar one', // no corresponding fieldtype, so theres no preprocessing
                    ],
                    [
                        '_id' => 'id-2-2', // comes from vue
                        'nested_numbers' => '6', // corresponding fieldtype has preprocessing
                        'nested_words' => 'more nested test two', // corresponding fieldtype has no preprocessing
                        'nested_foo' => 'more nested bar two', // no corresponding fieldtype, so theres no preprocessing
                    ]
                ]
            ]
        ]);

        $this->assertSame([
            [
                'numbers' => 2,
                'words' => 'test',
                'foo' => 'bar',
                'nested_grid' => [
                    [
                        'nested_numbers' => 3,
                        'nested_words' => 'nested test one',
                        'nested_foo' => 'nested bar one',
                    ],
                    [
                        'nested_numbers' => 4,
                        'nested_words' => 'nested test two',
                        'nested_foo' => 'nested bar two',
                    ]
                ]
            ],
            [
                'numbers' => 3,
                'words' => 'more test',
                'foo' => 'more bar',
                'nested_grid' => [
                    [
                        'nested_numbers' => 5,
                        'nested_words' => 'more nested test one',
                        'nested_foo' => 'more nested bar one',
                    ],
                    [
                        'nested_numbers' => 6,
                        'nested_words' => 'more nested test two',
                        'nested_foo' => 'more nested bar two',
                    ]
                ]
            ]
        ], $field->process()->value());
    }
}





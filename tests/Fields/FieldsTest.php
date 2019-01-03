<?php

namespace Tests\Fields;

use Tests\TestCase;
use Statamic\Fields\Field;
use Statamic\Fields\Fields;
use Statamic\Fields\Fieldset;
use Statamic\Fields\Fieldtype;
use Illuminate\Support\Collection;
use Facades\Statamic\Fields\FieldRepository;
use Facades\Statamic\Fields\FieldsetRepository;
use Facades\Statamic\Fields\FieldtypeRepository;

class FieldsTest extends TestCase
{
    /** @test */
    function it_converts_to_a_collection()
    {
        $fields = new Fields;

        tap($fields->all(), function ($items) {
            $this->assertInstanceOf(Collection::class, $items);
            $this->assertCount(0, $items);
        });

        FieldRepository::shouldReceive('find')
            ->with('fieldset_one.field_one')
            ->andReturnUsing(function () {
                return new Field('field_one', ['type' => 'text']);
            });

        FieldRepository::shouldReceive('find')
            ->with('fieldset_one.field_two')
            ->andReturnUsing(function () {
                return new Field('field_one', ['type' => 'textarea']);
            });

        FieldsetRepository::shouldReceive('find')
            ->with('fieldset_three')
            ->andReturnUsing(function () {
                return (new Fieldset)->setHandle('fieldset_three')->setContents(['fields' => [
                    'foo' => ['type' => 'textarea'],
                    'bar' => ['type' => 'text'],
                ]]);
            });

        $fields->setItems([
            [
                'handle' => 'one',
                'field' => 'fieldset_one.field_one'
            ],
            [
                'handle' => 'two',
                'field' => 'fieldset_one.field_two'
            ],
            [
                'handle' => 'three',
                'field' => [
                    'type' => 'textarea',
                ]
            ],
            [
                'import' => 'fieldset_three',
                'prefix' => 'a_',
            ],
            [
                'import' => 'fieldset_three',
                'prefix' => 'b_',
            ]
        ]);

        tap($fields->all(), function ($items) {
            $this->assertCount(7, $items);
            $this->assertEveryItemIsInstanceOf(Field::class, $items);
            $handles = ['one', 'two', 'three', 'a_foo', 'a_bar', 'b_foo', 'b_bar'];
            $this->assertEquals($handles, $items->map->handle()->values()->all());
            $this->assertEquals($handles, $items->keys()->all());
            $this->assertEquals(['text', 'textarea', 'textarea', 'textarea', 'text', 'textarea', 'text'], $items->map->type()->values()->all());
        });
    }

    /** @test */
    function it_gets_a_field_in_a_fieldset_when_given_a_reference()
    {
        $existing = new Field('bar', [
            'type' => 'textarea',
            'var_one' => 'one',
            'var_two' => 'two',
        ]);

        FieldRepository::shouldReceive('find')->with('foo.bar')->once()->andReturn($existing);

        $fields = (new Fields)->createFields([
            'handle' => 'test',
            'field' => 'foo.bar',
        ]);

        $this->assertTrue(is_array($fields));
        $this->assertCount(1, $fields);
        $field = $fields[0];
        $this->assertEquals('test', $field->handle());
        $this->assertEquals([
            'type' => 'textarea',
            'var_one' => 'one',
            'var_two' => 'two',
        ], $field->config());
    }

    /** @test */
    function it_can_override_the_config_in_a_referenced_field()
    {
        $existing = new Field('bar', [
            'type' => 'textarea',
            'var_one' => 'one',
            'var_two' => 'two',
        ]);

        FieldRepository::shouldReceive('find')->with('foo.bar')->once()->andReturn($existing);

        $fields = (new Fields)->createFields([
            'handle' => 'test',
            'field' => 'foo.bar',
            'config' => [
                'var_one' => 'overridden'
            ]
        ]);

        $this->assertTrue(is_array($fields));
        $this->assertCount(1, $fields);
        $field = $fields[0];
        $this->assertEquals('test', $field->handle());
        $this->assertEquals([
            'type' => 'textarea',
            'var_one' => 'overridden',
            'var_two' => 'two',
        ], $field->config());
    }

    /** @test */
    function it_throws_an_exception_when_an_invalid_field_reference_is_encountered()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Field foo.bar not found.');
        FieldRepository::shouldReceive('find')->with('foo.bar')->once()->andReturnNull();

        (new Fields)->createFields([
            'handle' => 'test',
            'field' => 'foo.bar'
        ]);
    }

    /** @test */
    function it_imports_the_fields_from_an_entire_fieldset_inline()
    {
        $fieldset = (new Fieldset)->setHandle('partial')->setContents([
            'fields' => [
                'one' => [
                    'type' => 'text'
                ],
                'two' => [
                    'type' => 'textarea'
                ]
            ]
        ]);

        FieldsetRepository::shouldReceive('find')->with('partial')->once()->andReturn($fieldset);

        $fields = (new Fields)->createFields([
            'import' => 'partial',
        ]);

        $this->assertTrue(is_array($fields));
        $this->assertCount(2, $fields);
        $this->assertEquals('one', $fields[0]->handle());
        $this->assertEquals('two', $fields[1]->handle());
    }

    /** @test */
    function it_prefixes_the_handles_of_imported_fieldsets()
    {
        $fieldset = (new Fieldset)->setHandle('partial')->setContents([
            'fields' => [
                'one' => [
                    'type' => 'text'
                ],
                'two' => [
                    'type' => 'textarea'
                ]
            ]
        ]);

        FieldsetRepository::shouldReceive('find')->with('partial')->once()->andReturn($fieldset);

        $fields = (new Fields)->createFields([
            'import' => 'partial',
            'prefix' => 'test_',
        ]);

        $this->assertTrue(is_array($fields));
        $this->assertCount(2, $fields);
        $this->assertEquals('test_one', $fields[0]->handle());
        $this->assertEquals('test_two', $fields[1]->handle());
    }

    /** @test */
    function it_throws_exception_when_trying_to_import_a_non_existent_fieldset()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Fieldset test_partial not found.');
        FieldsetRepository::shouldReceive('find')->with('test_partial')->once()->andReturnNull();

        (new Fields)->createFields([
            'import' => 'test_partial'
        ]);
    }

    /** @test */
    function it_merges_with_other_fields()
    {
        FieldRepository::shouldReceive('find')
            ->with('fieldset_one.field_one')
            ->andReturnUsing(function () {
                return new Field('field_one', ['type' => 'text']);
            });

        FieldRepository::shouldReceive('find')
            ->with('fieldset_one.field_two')
            ->andReturnUsing(function () {
                return new Field('field_one', ['type' => 'textarea']);
            });

        $fields = new Fields([
            [
                'handle' => 'one',
                'field' => 'fieldset_one.field_one'
            ]
        ]);

        $second = new Fields([
            [
                'handle' => 'two',
                'field' => 'fieldset_one.field_two'
            ]
        ]);

        $merged = $fields->merge($second);

        $this->assertCount(1, $fields->all());
        $this->assertCount(2, $items = $merged->all());
        $this->assertEquals(['one', 'two'], $items->map->handle()->values()->all());
        $this->assertEquals(['text', 'textarea'], $items->map->type()->values()->all());
    }

    /** @test */
    function converts_to_array_suitable_for_rendering_fields_in_publish_component()
    {
        FieldRepository::shouldReceive('find')
            ->with('fieldset_one.field_one')
            ->andReturnUsing(function () {
                return new Field('field_one', [
                    'type' => 'text',
                    'display' => 'One',
                    'instructions' => 'One instructions',
                    'validate' => 'required|min:2',
                ]);
            });

        FieldRepository::shouldReceive('find')
            ->with('fieldset_one.field_two')
            ->andReturnUsing(function () {
                return new Field('field_two', [
                    'type' => 'textarea',
                    'display' => 'Two',
                    'instructions' => 'Two instructions',
                    'validate' => 'min:2'
                ]);
            });

        $fields = new Fields([
            'one' => [ // use keys to ensure they get stripped out
                'handle' => 'one',
                'field' => 'fieldset_one.field_one'
            ],
            'two' => [
                'handle' => 'two',
                'field' => 'fieldset_one.field_two'
            ]
        ]);

        $this->assertEquals([
            [
                'handle' => 'one',
                'type' => 'text',
                'display' => 'One',
                'instructions' => 'One instructions',
                'required' => true,
                'validate' => 'required|min:2'
            ],
            [
                'handle' => 'two',
                'type' => 'textarea',
                'display' => 'Two',
                'instructions' => 'Two instructions',
                'required' => false,
                'validate' => 'min:2'
            ]
        ], $fields->toPublishArray());
    }

    /** @test */
    function it_adds_values_to_fields()
    {
        FieldRepository::shouldReceive('find')->with('one')->andReturnUsing(function () {
            return new Field('one', []);
        });

        FieldRepository::shouldReceive('find')->with('two')->andReturnUsing(function () {
            return new Field('two', []);
        });

        $fields = new Fields([
            ['handle' => 'one', 'field' => 'one'],
            ['handle' => 'two', 'field' => 'two']
        ]);

        $this->assertEquals(['one' => null, 'two' => null], $fields->values());

        $return = $fields->addValues(['one' => 'foo', 'two' => 'bar', 'three' => 'baz']);

        $this->assertEquals($fields, $return);
        $this->assertEquals(['one' => 'foo', 'two' => 'bar'], $fields->values());
    }

    /** @test */
    function it_processes_each_fields_values_by_its_fieldtype()
    {
        FieldtypeRepository::shouldReceive('find')->with('fieldtype')->andReturn(new class extends Fieldtype {
            public function process($data) {
                return $data . ' processed';
            }
        });

        FieldRepository::shouldReceive('find')->with('one')->andReturnUsing(function () {
            return new Field('one', ['type' => 'fieldtype']);
        });
        FieldRepository::shouldReceive('find')->with('two')->andReturnUsing(function () {
            return new Field('two', ['type' => 'fieldtype']);
        });

        $fields = new Fields([
            ['handle' => 'one', 'field' => 'one'],
            ['handle' => 'two', 'field' => 'two']
        ]);

        $this->assertEquals(['one' => null, 'two' => null], $fields->values());

        $fields->addValues(['one' => 'foo', 'two' => 'bar', 'three' => 'baz']);

        $this->assertEquals([
            'one' => 'foo processed',
            'two' => 'bar processed'
        ], $fields->process()->values());
    }

    /** @test */
    function it_preprocesses_each_fields_values_by_its_fieldtype()
    {
        FieldtypeRepository::shouldReceive('find')->with('fieldtype')->andReturn(new class extends Fieldtype {
            public function preProcess($data) {
                return $data . ' preprocessed';
            }
        });

        FieldRepository::shouldReceive('find')->with('one')->andReturnUsing(function () {
            return new Field('one', ['type' => 'fieldtype']);
        });
        FieldRepository::shouldReceive('find')->with('two')->andReturnUsing(function () {
            return new Field('two', ['type' => 'fieldtype']);
        });

        $fields = new Fields([
            ['handle' => 'one', 'field' => 'one'],
            ['handle' => 'two', 'field' => 'two']
        ]);

        $this->assertEquals(['one' => null, 'two' => null], $fields->values());

        $fields->addValues(['one' => 'foo', 'two' => 'bar', 'three' => 'baz']);

        $this->assertEquals([
            'one' => 'foo preprocessed',
            'two' => 'bar preprocessed'
        ], $fields->preProcess()->values());
    }

    /** @test */
    function it_gets_meta_data_from_all_fields()
    {
        FieldtypeRepository::shouldReceive('find')->with('fieldtype')->andReturn(new class extends Fieldtype {
            public function preload() {
                return 'meta data from field ' . $this->field->handle() . ' is ' . ($this->field->value() * 2);
            }
        });

        FieldRepository::shouldReceive('find')->with('one')->andReturnUsing(function () {
            return new Field('one', ['type' => 'fieldtype']);
        });
        FieldRepository::shouldReceive('find')->with('two')->andReturnUsing(function () {
            return new Field('two', ['type' => 'fieldtype']);
        });

        $fields = (new Fields([
            ['handle' => 'one', 'field' => 'one'],
            ['handle' => 'two', 'field' => 'two']
        ]))->addValues(['one' => 10, 'two' => 20]);

        $this->assertEquals([
            'one' => 'meta data from field one is 20',
            'two' => 'meta data from field two is 40',
        ], $fields->meta()->all());
    }
}

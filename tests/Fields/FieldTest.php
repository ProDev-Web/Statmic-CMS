<?php

namespace Tests\Fields;

use Tests\TestCase;
use Statamic\Fields\Field;
use Statamic\Fields\Fields;
use Statamic\Fields\Fieldtype;
use Illuminate\Support\Collection;
use Tests\Fakes\Fieldtypes\PlainFieldtype;
use Facades\Statamic\Fields\FieldRepository;
use Facades\Statamic\Fields\FieldtypeRepository;
use Tests\Fakes\Fieldtypes\FieldtypeWithValidationRules;

class FieldTest extends TestCase
{
    /** @test */
    function it_gets_the_display_value()
    {
        $this->assertEquals(
            'Test Display Value',
            (new Field('test', ['display' => 'Test Display Value']))->display()
        );

        $this->assertEquals(
            'Test',
            (new Field('test', []))->display()
        );

        $this->assertEquals(
            'Test multi word handle and no explicit display',
            (new Field('test_multi_word_handle_and_no_explicit_display', []))->display()
        );
    }

    /** @test */
    function it_gets_instructions()
    {
        $this->assertEquals(
            'The instructions',
            (new Field('test', ['instructions' => 'The instructions']))->instructions()
        );

        $this->assertNull((new Field('test', []))->instructions());
    }

    /** @test */
    function it_determines_if_localizable()
    {
        $this->assertFalse((new Field('test', []))->isLocalizable());
        $this->assertFalse((new Field('test', ['localizable' => false]))->isLocalizable());
        $this->assertTrue((new Field('test', ['localizable' => true]))->isLocalizable());
    }

    /** @test */
    function it_gets_the_fieldtype()
    {
        $fieldtype = new class extends Fieldtype { };

        FieldtypeRepository::shouldReceive('find')
            ->with('the_fieldtype')
            ->andReturn($fieldtype);

        $field = new Field('test', ['type' => 'the_fieldtype']);

        $this->assertEquals($fieldtype, $field->fieldtype());
    }

    /** @test */
    function it_gets_validation_rules_from_field()
    {
        $fieldtype = new class extends Fieldtype {
            protected $rules = null;
        };

        FieldtypeRepository::shouldReceive('find')
            ->with('fieldtype_with_no_rules')
            ->andReturn($fieldtype);

        $field = new Field('test', [
            'type' => 'fieldtype_with_no_rules',
            'validate' => 'required|min:2'
        ]);

        $this->assertEquals([
            'test' => ['required', 'min:2']
        ], $field->rules());
    }

    /** @test */
    function it_gets_validation_rules_from_fieldtype()
    {
        $fieldtype = new class extends Fieldtype {
            protected $rules = 'min:2|max:5';
        };

        FieldtypeRepository::shouldReceive('find')
            ->with('fieldtype_with_rules')
            ->andReturn($fieldtype);

        $field = new Field('test', ['type' => 'fieldtype_with_rules']);

        $this->assertEquals([
            'test' => ['min:2', 'max:5']
        ], $field->rules());
    }

    /** @test */
    function it_merges_validation_rules_from_field_with_fieldtype()
    {
        $fieldtype = new class extends Fieldtype {
            protected $rules = 'min:2|max:5';
        };

        FieldtypeRepository::shouldReceive('find')
            ->with('fieldtype_with_rules')
            ->andReturn($fieldtype);

        $field = new Field('test', [
            'type' => 'fieldtype_with_rules',
            'validate' => 'required|array'
        ]);

        $this->assertEquals([
            'test' => ['required', 'array', 'min:2', 'max:5']
        ], $field->rules());
    }

    /** @test */
    function it_merges_extra_fieldtype_rules()
    {
        $fieldtype = new class extends Fieldtype {
            protected $extraRules = [
                'test.*.one' => 'required|min:2',
                'test.*.two' => 'max:2'
            ];
        };

        FieldtypeRepository::shouldReceive('find')
            ->with('fieldtype_with_extra_rules')
            ->andReturn($fieldtype);

        $field = new Field('test', [
            'type' => 'fieldtype_with_extra_rules',
            'validate' => 'required'
        ]);

        $this->assertEquals([
            'test' => ['required'],
            'test.*.one' => ['required', 'min:2'],
            'test.*.two' => ['max:2'],
        ], $field->rules());
    }

    /** @test */
    function it_checks_if_a_field_is_required_when_defined_in_field()
    {
        $fieldtype = new class extends Fieldtype {
            protected $rules = null;
        };

        FieldtypeRepository::shouldReceive('find')
            ->with('fieldtype_with_no_rules')
            ->andReturn($fieldtype);

        $requiredField = new Field('test', [
            'type' => 'fieldtype_with_no_rules',
            'validate' => 'required|min:2'
        ]);

        $optionalField = new Field('test', [
            'type' => 'fieldtype_with_no_rules',
            'validate' => 'min:2'
        ]);

        $this->assertTrue($requiredField->isRequired());
        $this->assertFalse($optionalField->isRequired());
    }

    /** @test */
    function it_checks_if_a_field_is_required_when_defined_in_fieldtype()
    {
        $fieldtype = new class extends Fieldtype {
            protected $rules = 'required|min:2';
        };

        FieldtypeRepository::shouldReceive('find')
            ->with('fieldtype_with_rules')
            ->andReturn($fieldtype);

        $field = new Field('test', [
            'type' => 'fieldtype_with_rules',
            'validate' => 'min:2'
        ]);

        $this->assertTrue($field->isRequired());
    }

    /** @test */
    function converts_to_array_suitable_for_rendering_fields_in_publish_component()
    {
        FieldtypeRepository::shouldReceive('find')
            ->with('example')
            ->andReturn(new class extends Fieldtype {
                protected $configFields = [
                    'a_config_field_with_pre_processing' => ['type' => 'with_processing'],
                    'a_config_field_without_pre_processing' => ['type' => 'without_processing']
                ];
            });

            FieldtypeRepository::shouldReceive('find')
                ->with('with_processing')
                ->andReturn(new class extends Fieldtype {
                    public function preProcess($data) {
                        return $data . ' preprocessed';
                    }
                });

            FieldtypeRepository::shouldReceive('find')
                ->with('without_processing')
                ->andReturn(new class extends Fieldtype {
                    public function preProcess($data) {
                        return $data;
                    }
                });

        $field = new Field('test', [
            'type' => 'example',
            'display' => 'Test Field',
            'instructions' => 'Test instructions',
            'validate' => 'required',
            'a_config_field_with_pre_processing' => 'foo',
            'a_config_field_without_pre_processing' => 'foo',
        ]);

        $this->assertEquals([
            'handle' => 'test',
            'type' => 'example',
            'display' => 'Test Field',
            'instructions' => 'Test instructions',
            'required' => true,
            'validate' => 'required',
            'a_config_field_with_pre_processing' => 'foo preprocessed',
            'a_config_field_without_pre_processing' => 'foo',
        ], $field->toPublishArray());
    }

    /** @test */
    function it_gets_the_value()
    {
        $field = (new Field('test', ['type' => 'fieldtype']));
        $this->assertNull($field->value());

        $return = $field->setValue('foo');

        $this->assertEquals($field, $return);
        $this->assertEquals('foo', $field->value());
    }

    /** @test */
    function it_processes_the_value_through_its_fieldtype()
    {
        FieldtypeRepository::shouldReceive('find')
            ->with('fieldtype')
            ->andReturn(new class extends Fieldtype {
                public function process($data) {
                    return $data . ' processed';
                }
            });

        $field = (new Field('test', ['type' => 'fieldtype']))->setValue('foo');

        $this->assertEquals('foo processed', $field->process()->value());
    }

    /** @test */
    function it_preprocesses_the_value_through_its_fieldtype()
    {
        FieldtypeRepository::shouldReceive('find')
            ->with('fieldtype')
            ->andReturn(new class extends Fieldtype {
                public function preProcess($data) {
                    return $data . ' preprocessed';
                }
            });

        $field = (new Field('test', ['type' => 'fieldtype']))->setValue('foo');

        $this->assertEquals('foo preprocessed', $field->preProcess()->value());
    }

    /** @test */
    function preprocessing_a_field_with_no_value_will_take_the_default_from_the_field()
    {
        FieldtypeRepository::shouldReceive('find')
            ->with('fieldtype')
            ->andReturn(new class extends Fieldtype {
                public function preProcess($data) {
                    return $data . ' preprocessed';
                }
            });

        $field = (new Field('test', [
            'type' => 'fieldtype',
            'default' => 'field defined default',
        ]));

        $this->assertEquals('field defined default preprocessed', $field->preProcess()->value());
    }

    /** @test */
    function preprocessing_a_field_with_no_value_and_no_field_defined_default_value_will_take_the_default_from_the_fieldtype()
    {
        FieldtypeRepository::shouldReceive('find')
            ->with('fieldtype')
            ->andReturn(new class extends Fieldtype {
                public function preProcess($data) {
                    return $data . ' preprocessed';
                }
                public function defaultValue() {
                    return 'fieldtype defined default';
                }
            });

        $field = (new Field('test', ['type' => 'fieldtype']));

        $this->assertEquals('fieldtype defined default preprocessed', $field->preProcess()->value());
    }

    /** @test */
    function converting_to_an_array_will_inline_the_handle()
    {
        $field = new Field('the_handle', ['foo' => 'bar']);

        $this->assertEquals([
            'handle' => 'the_handle',
            'foo' => 'bar',
        ], $field->toArray());
    }

    /** @test */
    function it_gets_and_sets_the_config()
    {
        $field = new Field('the_handle', ['foo' => 'bar']);
        $this->assertEquals(['foo' => 'bar'], $field->config());

        $return = $field->setConfig(['bar' => 'baz']);

        $this->assertEquals($field, $return);
        $this->assertEquals(['bar' => 'baz'], $field->config());
    }
}

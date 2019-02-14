<?php

namespace Tests\Fields;

use Tests\TestCase;
use Statamic\Fields\Field;
use Statamic\Fields\Fields;
use Statamic\Fields\Section;
use Statamic\Fields\Blueprint;
use Illuminate\Support\Collection;
use Statamic\API\Field as FieldAPI;
use Facades\Statamic\Fields\FieldRepository;
use Facades\Statamic\Fields\BlueprintRepository;

class BlueprintTest extends TestCase
{
    /** @test */
    function it_gets_the_handle()
    {
        $blueprint = new Blueprint;
        $this->assertNull($blueprint->handle());

        $return = $blueprint->setHandle('test');

        $this->assertEquals($blueprint, $return);
        $this->assertEquals('test', $blueprint->handle());
    }

    /** @test */
    function it_gets_contents()
    {
        $blueprint = new Blueprint;
        $this->assertEquals([], $blueprint->contents());

        $contents = [
            'sections' => [
                'main' => [
                    'fields' => ['one' => ['type' => 'text']]
                ]
            ]
        ];

        $return = $blueprint->setContents($contents);

        $this->assertEquals($blueprint, $return);
        $this->assertEquals($contents, $blueprint->contents());
    }

    /** @test */
    function it_gets_the_title()
    {
        $blueprint = (new Blueprint)->setContents([
            'title' => 'Test'
        ]);

        $this->assertEquals('Test', $blueprint->title());
    }

    /** @test */
    function the_title_falls_back_to_a_humanized_handle()
    {
        $blueprint = (new Blueprint)->setHandle('the_blueprint_handle');

        $this->assertEquals('The blueprint handle', $blueprint->title());
    }

    /** @test */
    function it_gets_sections()
    {
        $blueprint = new Blueprint;
        tap($blueprint->sections(), function ($sections) {
            $this->assertInstanceOf(Collection::class, $sections);
            $this->assertCount(0, $sections);
        });

        $contents = [
            'sections' => [
                'section_one' => [
                    'fields' => ['one' => ['type' => 'text']]
                ],
                'section_two' => [
                    'fields' => ['two' => ['type' => 'text']]
                ]
            ]
        ];

        $blueprint->setContents($contents);

        tap($blueprint->sections(), function ($sections) {
            $this->assertCount(2, $sections);
            $this->assertEveryItemIsInstanceOf(Section::class, $sections);
            $this->assertEquals(['section_one', 'section_two'], $sections->map->handle()->values()->all());
        });
    }

    /** @test */
    function it_puts_top_level_fields_into_a_main_section()
    {
        $blueprint = new Blueprint;
        $this->assertEquals([], $blueprint->contents());

        $blueprint->setContents([
            'fields' => ['one' => ['type' => 'text']]
        ]);

        $this->assertEquals([
            'sections' => [
                'main' => [
                    'fields' => ['one' => ['type' => 'text']]
                ]
            ]
        ], $blueprint->contents());
    }

    /** @test */
    function it_gets_fields()
    {
        $blueprint = new Blueprint;
        tap($blueprint->fields(), function ($fields) {
            $this->assertInstanceOf(Fields::class, $fields);
            $this->assertCount(0, $fields->all());
        });

        FieldRepository::shouldReceive('find')
            ->with('fieldset_one.field_one')
            ->andReturn(new Field('field_one', ['type' => 'text']));
        FieldRepository::shouldReceive('find')
            ->with('fieldset_one.field_two')
            ->andReturn(new Field('field_one', ['type' => 'textarea']));

        $blueprint->setContents($contents = [
            'sections' => [
                'section_one' => [
                    'fields' => [
                        [
                            'handle' => 'one',
                            'field' => 'fieldset_one.field_one'
                        ]
                    ]
                ],
                'section_two' => [
                    'fields' => [
                        [
                            'handle' => 'two',
                            'field' => 'fieldset_one.field_two'
                        ]
                    ]
                ]
            ]
        ]);

        $this->assertTrue($blueprint->hasField('one'));
        $this->assertTrue($blueprint->hasField('two'));
        $this->assertFalse($blueprint->hasField('three'));

        tap($blueprint->fields(), function ($fields) {
            $this->assertInstanceOf(Fields::class, $fields);
            tap($fields->all(), function ($items) {
                $this->assertCount(2, $items);
                $this->assertEveryItemIsInstanceOf(Field::class, $items);
                $this->assertEquals(['one', 'two'], $items->map->handle()->values()->all());
                $this->assertEquals(['text', 'textarea'], $items->map->type()->values()->all());
            });
        });
    }

    /** @test */
    function converts_to_array_suitable_for_rendering_fields_in_publish_component()
    {
        FieldRepository::shouldReceive('find')
            ->with('fieldset_one.field_one')
            ->andReturn(new Field('field_one', [
                'type' => 'text',
                'display' => 'One',
                'instructions' => 'One instructions',
                'validate' => 'required|min:2',
            ]));
        FieldRepository::shouldReceive('find')
            ->with('fieldset_one.field_two')
            ->andReturn(new Field('field_two', [
                'type' => 'textarea',
                'display' => 'Two',
                'instructions' => 'Two instructions',
                'validate' => 'min:2'
            ]));

        $blueprint = (new Blueprint)->setHandle('test')->setContents($contents = [
            'title' => 'Test',
            'sections' => [
                'section_one' => [
                    'fields' => [
                        [
                            'handle' => 'one',
                            'field' => 'fieldset_one.field_one'
                        ]
                    ]
                ],
                'section_two' => [
                    'fields' => [
                        [
                            'handle' => 'two',
                            'field' => 'fieldset_one.field_two'
                        ]
                    ]
                ]
            ]
        ]);

        $this->assertEquals([
            'title' => 'Test',
            'handle' => 'test',
            'sections' => [
                [
                    'display' => 'Section one',
                    'handle' => 'section_one',
                    'fields' => [
                        [
                            'handle' => 'one',
                            'type' => 'text',
                            'display' => 'One',
                            'instructions' => 'One instructions',
                            'required' => true,
                            'validate' => 'required|min:2'
                        ]
                    ]
                ],
                [
                    'display' => 'Section two',
                    'handle' => 'section_two',
                    'fields' => [
                        [
                            'handle' => 'two',
                            'type' => 'textarea',
                            'display' => 'Two',
                            'instructions' => 'Two instructions',
                            'required' => false,
                            'validate' => 'min:2'
                        ]
                    ]
                ]
            ]
        ], $blueprint->toPublishArray());
    }

    /** @test */
    function it_saves_through_the_repository()
    {
        BlueprintRepository::shouldReceive('save')->with($blueprint = new Blueprint)->once();

        $return = $blueprint->save();

        $this->assertEquals($blueprint, $return);
    }
}

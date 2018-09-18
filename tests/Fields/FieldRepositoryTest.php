<?php

namespace Tests\Fields;

use Mockery;
use Tests\TestCase;
use Statamic\Fields\Field;
use Statamic\Fields\Fieldset;
use Statamic\Fields\FieldRepository;
use Statamic\Fields\FieldsetRepository;

class FieldRepositoryTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $fieldsets = Mockery::mock(FieldsetRepository::class);

        $fieldsets->shouldReceive('find')->with('test')->andReturnUsing(function () {
            return (new Fieldset)->setHandle('test')->setContents([
                'title' => 'Test',
                'fields' => [
                    'one' => ['type' => 'textarea', 'display' => 'First Field'],
                ]
            ]);
        });

        $fieldsets->shouldReceive('find')->with('unknown')->andReturnNull();

        $this->repo = new FieldRepository($fieldsets);
    }

    /** @test */
    function it_gets_a_field_within_a_fieldset()
    {
        $field = $this->repo->find('test.one');

        $this->assertInstanceOf(Field::class, $field);
        $this->assertEquals('one', $field->handle());
        $this->assertEquals('First Field', $field->display());
        $this->assertEquals('textarea', $field->type());
    }

    /** @test */
    function unknown_field_in_valid_fieldset_returns_null()
    {
        $this->assertNull($this->repo->find('test.unknown'));
    }

    /** @test */
    function it_returns_null_if_fieldset_doesnt_exist()
    {
        $this->assertNull($this->repo->find('unknown.test'));
    }

    /** @test */
    function it_returns_null_if_fieldset_and_field_are_not_both_provided()
    {
        $this->assertNull($this->repo->find('test'));
    }
}

<?php

namespace Tests;

use Tests\TestCase;
use Statamic\CP\Column;

class ColumnTest extends TestCase
{
    /** @test */
    function it_can_make_a_basic_column()
    {
        $column = Column::make('first_name');

        $this->assertEquals('first_name', $column->field());
        $this->assertEquals('First Name', $column->Label());
        $this->assertTrue($column->visible());
    }

    /** @test */
    function it_can_explicitly_set_data_and_serialize_to_json()
    {
        $column = Column::make()
            ->field('bars')
            ->label('Ripped')
            ->visible(false);

        $json = json_decode(json_encode($column));

        $this->assertEquals('bars', $json->field);
        $this->assertEquals('Ripped', $json->label);
        $this->assertFalse($json->visible);
    }
}

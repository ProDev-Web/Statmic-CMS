<?php

namespace Tests;

use Tests\TestCase;
use Statamic\API\Str;
use Statamic\FluentlyGetsAndSets;

class FluentlyGetsAndSetsTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->entry = new Entry;
    }

    /** @test */
    function it_can_get_and_set_a_protected_property()
    {
        $this->assertNull($this->entry->blueprint());

        $this->entry->blueprint('post');

        $this->assertEquals('post', $this->entry->blueprint());
    }

    /** @test */
    function it_can_get_and_set_back_to_null()
    {
        $this->assertEquals('Jesse', $this->entry->publishedBy());

        $this->entry->publishedBy(null);

        $this->assertNull($this->entry->publishedBy());
    }

    /** @test */
    function it_can_get_and_set_with_custom_get_and_set_logic()
    {
        $this->assertNull($this->entry->title());

        $this->entry->title('lol cat');

        $this->assertEquals('Lol Cats', $this->entry->title());
    }

    /** @test */
    function it_can_run_custom_after_setter_logic()
    {
        $this->assertNull($this->entry->route());

        $this->entry->route('login');

        $this->assertEquals('login', $this->entry->route());
        $this->assertEquals('login', $this->entry->url);
    }

    /** @test */
    function it_can_set_fluently()
    {
        $this->entry
            ->title('lol cat')
            ->blueprint('post')
            ->publishedBy('Hoff');

        $this->assertEquals('Lol Cats', $this->entry->title());
        $this->assertEquals('post', $this->entry->blueprint());
        $this->assertEquals('Hoff', $this->entry->publishedBy());
    }
}

class Entry
{
    use FluentlyGetsAndSets;

    protected $blueprint;

    protected $publishedBy = 'Jesse';

    public function blueprint($blueprint = null)
    {
        return $this->fluentlyGetOrSet('blueprint')->value($blueprint);
    }

    public function publishedBy($name = null)
    {
        return $this->fluentlyGetOrSet('publishedBy')->args(func_get_args());
    }

    public function title($title = null)
    {
        return $this->fluentlyGetOrSet('title')
            ->getter(function ($title) {
                return Str::title($title) ?: null;
            })
            ->setter(function ($title) {
                return Str::plural($title);
            })
            ->value($title);
    }

    public function route($route = null)
    {
        return $this->fluentlyGetOrSet('route')
            ->afterSetter(function ($route) {
                $this->url = $route;
            })
            ->value($route);
    }
}

<?php

namespace Tests\Routing;

use Facades\Tests\Factories\EntryFactory;
use Illuminate\Support\Facades\Route;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Tests\FakesViews;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class RoutesTest extends TestCase
{
    use FakesViews;
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        $this->withFakeViews();
    }

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app->booted(function () {
            Route::statamic('/basic-route-with-data', 'test', ['hello' => 'world']);

            Route::statamic('/basic-route-without-data', 'test');

            Route::statamic('/route-with-custom-layout', 'test', [
                'layout' => 'custom-layout',
                'hello' => 'world'
            ]);

            Route::statamic('/route-with-loaded-entry', 'test', [
                'hello' => 'world',
                'load' => 'pages-blog'
            ]);

            Route::statamic('/route-with-loaded-entry-by-uri', 'test', [
                'hello' => 'world',
                'load' => '/blog'
            ]);
        });
    }

    /** @test */
    function it_renders_a_view()
    {
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('test', 'Hello {{ hello }}');

        $this->get('/basic-route-with-data')
            ->assertOk()
            ->assertSee('Hello world');
    }

    /** @test */
    function it_renders_a_view_without_data()
    {
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('test', 'Hello {{ hello }}');

        $this->get('/basic-route-without-data')
            ->assertOk()
            ->assertSee('Hello ');
    }

    /** @test */
    function it_renders_a_view_with_custom_layout()
    {
        $this->viewShouldReturnRaw('custom-layout', 'Custom layout {{ template_content }}');
        $this->viewShouldReturnRaw('layout', 'Default layout');
        $this->viewShouldReturnRaw('test', 'Hello {{ hello }}');

        $this->get('/route-with-custom-layout')
            ->assertOk()
            ->assertSee('Custom layout Hello world');
    }

    /** @test */
    function it_loads_content()
    {
        EntryFactory::id('pages-blog')->collection('pages')->data(['title' => 'Blog'])->create();

        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('test', 'Hello {{ hello }} {{ title }} {{ id }}');

        $this->get('/route-with-loaded-entry')
            ->assertOk()
            ->assertSee('Hello world Blog pages-blog');
    }

    /** @test */
    function it_loads_content_by_uri()
    {
        $collection = Collection::make('pages')->route('/{slug}')->save();
        EntryFactory::id('pages-blog')->collection($collection)->slug('blog')->data(['title' => 'Blog'])->create();

        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('test', 'Hello {{ hello }} {{ title }} {{ id }}');

        $this->get('/route-with-loaded-entry-by-uri')
            ->assertOk()
            ->assertSee('Hello world Blog pages-blog');
    }
}

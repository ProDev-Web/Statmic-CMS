<?php

namespace Tests\API;

use Tests\TestCase;
use Statamic\API\Nav;
use Tests\FakesRoles;
use Statamic\API\User;
use Illuminate\Support\Facades\Route;

class NavTest extends TestCase
{
    use FakesRoles;

    public function setUp()
    {
        parent::setUp();

        Route::any('wordpress-importer', ['as' => 'statamic.cp.wordpress-importer.index']);
    }

    /** @test */
    function it_can_build_a_default_nav()
    {
        $expected = collect([
            'Content' => ['Collections', 'Structure', 'Taxonomies', 'Assets', 'Globals'],
            'Tools' => ['Forms', 'Updates', 'Utilities'],
            'Users' => ['Users', 'Groups', 'Permissions'],
            'Site' => ['Addons', 'Preferences', 'Fieldsets', 'Blueprints']
        ]);

        $this->actingAs(User::make()->makeSuper());

        $nav = Nav::build();

        $this->assertEquals($expected->keys(), $nav->keys());
        $this->assertEquals($expected->get('Content'), $nav->get('Content')->map->name()->all());
        $this->assertEquals($expected->get('Tools'), $nav->get('Tools')->map->name()->all());
        $this->assertEquals($expected->get('Users'), $nav->get('Users')->map->name()->all());
        $this->assertEquals($expected->get('Site'), $nav->get('Site')->map->name()->all());
    }

    /** @test */
    function is_can_create_a_nav_item()
    {
        $this->actingAs(User::make()->makeSuper());

        Nav::utilities('Wordpress Importer')
            ->route('wordpress-importer.index')
            ->icon('up-arrow')
            ->can('view updates');

        $item = Nav::build()->get('Utilities')->last();

        $this->assertEquals('Utilities', $item->section());
        $this->assertEquals('Wordpress Importer', $item->name());
        $this->assertEquals(config('app.url').'wordpress-importer', $item->url());
        $this->assertEquals(config('app.url').'wordpress-importer*', $item->currentClass());
        $this->assertEquals('up-arrow', $item->icon());
        $this->assertEquals('view updates', $item->authorization()->ability);
        $this->assertEquals('view updates', $item->can()->ability);
    }

    /** @test */
    function it_can_more_explicitly_create_a_nav_item()
    {
        $this->actingAs(User::make()->makeSuper());

        Nav::create('R2-D2')
            ->section('Droids')
            ->url('/r2');

        $item = Nav::build()->get('Droids')->first();

        $this->assertEquals('Droids', $item->section());
        $this->assertEquals('R2-D2', $item->name());
        $this->assertEquals('/r2', $item->url());
    }

    /** @test */
    function it_can_create_a_nav_item_with_a_more_custom_config()
    {
        $this->actingAs(User::make()->makeSuper());

        Nav::droids('C-3PO')
            ->currentClass('threepio*')
            ->url('/human-cyborg-relations')
            ->view('cp.nav.importer')
            ->can('index', 'DroidsClass');

        $item = Nav::build()->get('Droids')->first();

        $this->assertEquals('Droids', $item->section());
        $this->assertEquals('C-3PO', $item->name());
        $this->assertEquals('/human-cyborg-relations', $item->url());
        $this->assertEquals('cp.nav.importer', $item->view());
        $this->assertEquals('threepio*', $item->currentClass());
        $this->assertEquals('index', $item->authorization()->ability);
        $this->assertEquals('DroidsClass', $item->authorization()->arguments);
    }

    /** @test */
    function it_can_get_and_modify_an_existing_item()
    {
        $this->actingAs(User::make()->makeSuper());

        Nav::droids('WAC-47')
            ->url('/pit-droid')
            ->icon('droid');

        Nav::droids('WAC-47')
            ->url('/d-squad');

        $item = Nav::build()->get('Droids')->first();

        $this->assertEquals('Droids', $item->section());
        $this->assertEquals('WAC-47', $item->name());
        $this->assertEquals('droid', $item->icon());
        $this->assertEquals('/d-squad', $item->url());
    }

    /** @test */
    function it_doesnt_build_items_that_the_user_is_not_authorized_to_see()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $this->actingAs(User::make()->assignRole('test'));

        Nav::theEmpire('Death Star');

        $item = Nav::build()->get('The Empire')->first();

        $this->assertEquals('Death Star', Nav::build()->get('The Empire')->first()->name());

        Nav::theEmpire('Death Star')
            ->can('view death star');

        $this->assertNull(Nav::build()->get('The Empire'));
    }

    /** @test */
    function it_can_create_a_nav_item_with_children()
    {
        $this->actingAs(User::make()->makeSuper());

        Nav::droids('Battle Droids')
            ->url('/battle-droids')
            ->children([
                Nav::item('B1')->url('/b1'),
                Nav::item('B2')->url('/b2'),
                Nav::item('HK-47')->url('/hk-47'),
            ]);

        $item = Nav::build()->get('Droids')->first();

        $this->assertEquals('Battle Droids', $item->name());
        $this->assertEquals('B1', $item->children()->get(0)->name());
        $this->assertEquals('B2', $item->children()->get(1)->name());
        $this->assertEquals('HK-47', $item->children()->get(2)->name());
    }

    /** @test */
    function it_can_remove_a_nav_section()
    {
        $this->actingAs(User::make()->makeSuper());

        Nav::ships('Millenium Falcon')
            ->url('/millenium-falcon')
            ->icon('falcon');

        Nav::ships('X-Wing')
            ->url('/x-wing')
            ->icon('x-wing');

        $this->assertCount(2, Nav::build()->get('Ships'));

        Nav::remove('Ships');

        $this->assertNull(Nav::build()->get('Ships'));
    }

    /** @test */
    function it_can_remove_a_specific_nav_item()
    {
        $this->actingAs(User::make()->makeSuper());

        Nav::ships('Y-Wing')
            ->url('/y-wing')
            ->icon('y-wing');

        Nav::ships('A-Wing')
            ->url('/a-wing')
            ->icon('a-wing');

        $this->assertCount(2, Nav::build()->get('Ships'));

        Nav::remove('Ships', 'Y-Wing');

        $this->assertCount(1, $ships = Nav::build()->get('Ships'));
        $this->assertEquals('A-Wing', $ships->first()->name());
    }

    /** @test */
    function it_can_use_extend_to_defer_the_creation_of_a_nav_item_until_build_time()
    {
        $this->actingAs(User::make()->makeSuper());

        Nav::extend(function ($nav) {
            $nav->jedi('Yoda')->url('/yodas-hut')->icon('green-but-cute-alien');
        });

        $this->assertEmpty(Nav::items());

        $nav = Nav::build();

        $this->assertNotEmpty(Nav::items());
        $this->assertContains('Yoda', Nav::build()->get('Jedi')->map->name());
    }

    /** @test */
    function it_can_use_extend_to_remove_a_default_statamic_nav_item()
    {
        $this->actingAs(User::make()->makeSuper());

        $nav = Nav::build();

        $this->assertContains('Collections', Nav::build()->get('Content')->map->name());

        Nav::extend(function ($nav) {
            $nav->remove('Content', 'Collections');
        });

        $this->assertNotContains('Collections', Nav::build()->get('Content')->map->name());
    }
}

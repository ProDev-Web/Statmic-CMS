<?php

namespace Tests\Stache;

use Mockery;
use Tests\TestCase;
use Statamic\Facades\Data;
use Statamic\Facades\User;
use Statamic\Facades\Entry;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\Structure;
use Statamic\Stache\Stache;
use Statamic\Facades\Collection;
use Statamic\Stache\Fakes\YAML;
use Statamic\Facades\AssetContainer;
use Illuminate\Support\Facades\Cache;
use Statamic\Stache\Stores\BasicStore;
use Statamic\Stache\Stores\EntriesStore;
use Statamic\Stache\Stores\AggregateStore;
use Statamic\Stache\Stores\CollectionsStore;
use Statamic\Contracts\Structures\StructureRepository;

class FeatureTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->stache = tap($this->app->make('stache'), function ($stache) {
            $dir = __DIR__.'/__fixtures__';
            $stache->store('taxonomies')->directory($dir . '/content/taxonomies');
            $stache->store('collections')->directory($dir . '/content/collections');
            $stache->store('entries')->directory($dir . '/content/collections');
            $stache->store('structures')->directory($dir . '/content/structures');
            $stache->store('globals')->directory($dir . '/content/globals');
            $stache->store('asset-containers')->directory($dir . '/content/assets');
            $stache->store('users')->directory($dir . '/users');
        });
    }

    /** @test */
    function it_gets_all_collections()
    {
        $this->assertEquals(4, Collection::all()->count());
    }

    /** @test */
    function it_gets_all_entries()
    {
        $this->assertEquals(14, Entry::all()->count());
        $this->assertEquals(3, Entry::whereCollection('alphabetical')->count());
        $this->assertEquals(2, Entry::whereCollection('blog')->count());
        $this->assertEquals(3, Entry::whereCollection('numeric')->count());
        $this->assertEquals(6, Entry::whereCollection('pages')->count());
        $this->assertEquals(5, Entry::whereInCollection(['alphabetical', 'blog'])->count());
    }

    /** @test */
    function it_gets_entry()
    {
        $entry = Entry::find('blog-christmas');
        $this->assertEquals('Christmas', $entry->get('title'));
        $this->assertSame($entry, Data::find('entry::blog-christmas'));
        $this->assertSame($entry, Data::find('blog-christmas'));

        // ensure it only gets from the entries' store, not anywhere in the stache.
        $this->assertNull(Entry::find('users-john'));
    }

    /** @test */
    function it_gets_entry_by_slug()
    {
        $this->assertEquals('Christmas', Entry::findBySlug('christmas', 'blog', 'christmas')->get('title'));
    }

    /** @test */
    function it_gets_all_taxonomies()
    {
        $this->assertEquals(2, Taxonomy::all()->count());
    }

    /** @test */
    function it_gets_all_globals()
    {
        $this->assertEquals(2, GlobalSet::all()->count());
    }

    /** @test */
    function it_gets_globals()
    {
        $global = GlobalSet::find('globals-global');
        $this->assertEquals('Bar', $global->in('en')->get('foo'));
        $this->assertSame($global, Data::find('global::globals-global'));
        $this->assertSame($global, Data::find('globals-global'));
        $this->assertEquals('555-1234', GlobalSet::find('globals-contact')->in('en')->get('phone'));
    }

    /** @test */
    function it_gets_asset_containers()
    {
        $this->assertEquals(2, AssetContainer::all()->count());
    }

    /** @test */
    function it_gets_an_asset_container()
    {
        $this->assertEquals('Main Assets', AssetContainer::find('main')->title());
        $this->assertEquals('Another Asset Container', AssetContainer::find('another')->title());
    }

    /** @test */
    function it_gets_users()
    {
        $this->assertEquals(2, User::all()->count());
    }

    /** @test */
    function it_gets_a_user()
    {
        $user = User::find('users-john');
        $this->assertEquals('users-john', $user->id());
        $this->assertEquals('John Smith', $user->get('name'));
        $this->assertEquals('john@example.com', $user->email());
        $this->assertSame($user, Data::find('user::users-john'));
        $this->assertSame($user, Data::find('users-john'));
    }

    /** @test */
    function it_gets_an_entry_by_uri()
    {
        $entry = Entry::findByUri('/numeric/two');
        $this->assertEquals('numeric-two', $entry->id());
        $this->assertEquals('Two', $entry->get('title'));

        $this->assertNull(Entry::findByUri('/unknown'));
    }

    /** @test */
    function it_gets_an_entry_in_structure_by_uri()
    {
        $entry = Entry::findByUri('/about/board/directors');
        $this->assertEquals('pages-directors', $entry->id());
        $this->assertEquals('Directors', $entry->title());
    }

    /** @test */
    function it_gets_structures()
    {
        $this->assertEquals(2, Structure::all()->count());
    }

    /** @test */
    function it_gets_a_structure()
    {
        $structure = Structure::find('pages');
        $this->assertEquals('pages', $structure->handle());
        // TODO: Some more assertions
    }

    /** @test */
    function it_saves_structures()
    {
        $structure = Structure::find('pages');

        $repo = Mockery::mock(StructureRepository::class);
        $repo->shouldReceive('save')->with($structure);
        $this->app->instance(StructureRepository::class, $repo);

        $structure->save();
    }

    /** @test */
    function saving_a_collection_writes_it_to_file()
    {
        Collection::make('new')
            ->title('New Collection')
            ->defaultPublishState(false)
            ->orderable(true)
            ->dated(true)
            ->revisionsEnabled(true)
            ->cascade(['foo' => 'bar'])
            ->save();

        $this->assertStringEqualsFile(
            $path = __DIR__.'/__fixtures__/content/collections/new.yaml',
            "title: 'New Collection'\norderable: true\nrevisions: true\ndate: true\ndefault_status: draft\ninject:\n  foo: bar\n"
        );
        @unlink($path);
    }


    /** @test */
    function saving_an_asset_container_writes_it_to_file()
    {
        AssetContainer::make('new')->title('New Container')->save();

        $this->assertStringEqualsFile(
            $path = __DIR__.'/__fixtures__/content/assets/new.yaml',
            "title: 'New Container'\n"
        );
        @unlink($path);
    }

    /** @test */
    function saving_a_taxonomy_writes_it_to_file()
    {
        Taxonomy::make('new')->title('New Taxonomy')->save();

        $this->assertStringEqualsFile(
            $path = __DIR__.'/__fixtures__/content/taxonomies/new.yaml',
            "title: 'New Taxonomy'\n"
        );
        @unlink($path);
    }

    /** @test */
    function saving_a_global_set_writes_it_to_file()
    {
        $global = GlobalSet::make()
            ->id('123')
            ->handle('new')
            ->title('New Global Set');

        $global->addLocalization(
            $global->makeLocalization('en')->data(['foo' => 'bar'])
        );

        $global->save();

        $this->assertStringEqualsFile(
            $path = __DIR__.'/__fixtures__/content/globals/new.yaml',
            "id: '123'\ntitle: 'New Global Set'\ndata:\n  foo: bar\n"
        );
        @unlink($path);
    }

    /** @test */
    function saving_an_entry_writes_it_to_file()
    {
        $entry = tap(Entry::make()
            ->locale('en')
            ->id('123')
            ->collection(Collection::findByHandle('blog'))
            ->slug('test-entry')
            ->date('2017-07-04')
            ->data(['title' => 'Test Entry', 'foo' => 'bar'])
        )->save();

        $this->assertFileExists(__DIR__.'/__fixtures__/content/collections/blog/2017-07-04.test-entry.md');

        $entry->delete();
    }
}

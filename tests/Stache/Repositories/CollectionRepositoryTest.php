<?php

namespace Tests\Stache\Repositories;

use Tests\TestCase;
use Statamic\Stache\Stache;
use Statamic\Data\Entries\Collection;
use Statamic\Stache\Stores\EntriesStore;
use Statamic\Stache\Stores\StructuresStore;
use Statamic\Stache\Stores\CollectionsStore;
use Statamic\API\Collection as CollectionAPI;
use Statamic\Stache\Repositories\CollectionRepository;
use Illuminate\Support\Collection as IlluminateCollection;

class CollectionRepositoryTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $stache = (new Stache)->sites(['en', 'fr']);
        $this->app->instance(Stache::class, $stache);
        $this->directory = __DIR__.'/../__fixtures__/content/collections';
        $stache->registerStores([
            (new CollectionsStore($stache, app('files')))->directory($this->directory),
            (new EntriesStore($stache, app('files')))->directory($this->directory),
            (new StructuresStore($stache, app('files')))->directory(__DIR__.'/../__fixtures__/content/structures'),
        ]);

        $this->repo = new CollectionRepository($stache);
    }

    /** @test */
    function it_gets_all_collections()
    {
        $collections = $this->repo->all();

        $this->assertInstanceOf(IlluminateCollection::class, $collections);
        $this->assertCount(4, $collections);
        $this->assertEveryItemIsInstanceOf(Collection::class, $collections);

        $ordered = $collections->sortBy->handle()->values();
        $this->assertEquals(['alphabetical', 'blog', 'numeric', 'pages'], $ordered->map->handle()->all());
        $this->assertEquals(['Alphabetical', 'Blog', 'Numeric', 'Pages'], $ordered->map->title()->all());
    }

    /** @test */
    function it_gets_a_collection_by_handle()
    {
        tap($this->repo->findByHandle('alphabetical'), function ($collection) {
            $this->assertInstanceOf(Collection::class, $collection);
            $this->assertEquals('alphabetical', $collection->handle());
            $this->assertEquals('Alphabetical', $collection->title());
        });

        tap($this->repo->findByHandle('blog'), function ($collection) {
            $this->assertInstanceOf(Collection::class, $collection);
            $this->assertEquals('blog', $collection->handle());
            $this->assertEquals('Blog', $collection->title());
        });

        tap($this->repo->findByHandle('numeric'), function ($collection) {
            $this->assertInstanceOf(Collection::class, $collection);
            $this->assertEquals('numeric', $collection->handle());
            $this->assertEquals('Numeric', $collection->title());
        });

        tap($this->repo->findByHandle('pages'), function ($collection) {
            $this->assertInstanceOf(Collection::class, $collection);
            $this->assertEquals('pages', $collection->handle());
            $this->assertEquals('Pages', $collection->title());
        });

        $this->assertNull($this->repo->findByHandle('unknown'));
    }

    /** @test */
    function it_saves_a_collection_to_the_stache_and_to_a_file()
    {
        $collection = CollectionAPI::create('new');
        $collection->data(['foo' => 'bar']);
        $this->assertNull($this->repo->findByHandle('new'));

        $this->repo->save($collection);

        $this->assertNotNull($item = $this->repo->findByHandle('new'));
        $this->assertEquals(['foo' => 'bar'], $item->data());
        $this->assertTrue(file_exists($this->directory.'/new.yaml'));
        @unlink($this->directory.'/new.yaml');
    }
}

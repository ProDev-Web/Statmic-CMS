<?php

namespace Tests\Stache\Repositories;

use Tests\TestCase;
use Statamic\Stache\Stache;
use Statamic\Stache\Stores\EntriesStore;
use Statamic\Contracts\Data\Entries\Entry;
use Statamic\Data\Entries\EntryCollection;
use Statamic\Stache\Stores\StructuresStore;
use Statamic\Stache\Stores\CollectionsStore;
use Statamic\Stache\Repositories\EntryRepository;

class EntryRepositoryTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $stache = (new Stache)->sites(['en', 'fr']);
        $this->app->instance(Stache::class, $stache);
        $stache->registerStores([
            (new CollectionsStore($stache, app('files')))->directory(__DIR__.'/../__fixtures__/content/collections'),
            (new EntriesStore($stache, app('files')))->directory(__DIR__.'/../__fixtures__/content/collections'),
            (new StructuresStore($stache, app('files')))->directory(__DIR__.'/../__fixtures__/content/structures'),
        ]);

        $this->repo = new EntryRepository($stache);
    }

    /** @test */
    function it_gets_all_entries()
    {
        $entries = $this->repo->all();

        $this->assertInstanceOf(EntryCollection::class, $entries);
        $this->assertCount(14, $entries);
        $this->assertEveryItemIsInstanceOf(Entry::class, $entries);
        $this->assertEquals([
            'alphabetical-alpha',
            'alphabetical-bravo',
            'alphabetical-zulu',
            'blog-christmas',
            'blog-fourth-of-july',
            'numeric-one',
            'numeric-three',
            'numeric-two',
            'pages-about',
            'pages-blog',
            'pages-board',
            'pages-contact',
            'pages-directors',
            'pages-home',
        ], $entries->map->id()->sort()->values()->all());
    }

    /** @test */
    function it_gets_entries_from_a_collection()
    {
        tap($this->repo->whereCollection('alphabetical'), function ($entries) {
            $this->assertInstanceOf(EntryCollection::class, $entries);
            $this->assertCount(3, $entries);
            $this->assertEveryItemIsInstanceOf(Entry::class, $entries);
            $this->assertEveryItem($entries, function ($item) {
                return $item->collectionName() === 'alphabetical';
            });
        });

        tap($this->repo->whereCollection('blog'), function ($entries) {
            $this->assertInstanceOf(EntryCollection::class, $entries);
            $this->assertCount(2, $entries);
            $this->assertEveryItemIsInstanceOf(Entry::class, $entries);
            $this->assertEveryItem($entries, function ($item) {
                return $item->collectionName() === 'blog';
            });
        });

        tap($this->repo->whereCollection('numeric'), function ($entries) {
            $this->assertInstanceOf(EntryCollection::class, $entries);
            $this->assertCount(3, $entries);
            $this->assertEveryItemIsInstanceOf(Entry::class, $entries);
            $this->assertEveryItem($entries, function ($item) {
                return $item->collectionName() === 'numeric';
            });
        });

        tap($this->repo->whereCollection('pages'), function ($entries) {
            $this->assertInstanceOf(EntryCollection::class, $entries);
            $this->assertCount(6, $entries);
            $this->assertEveryItemIsInstanceOf(Entry::class, $entries);
            $this->assertEveryItem($entries, function ($item) {
                return $item->collectionName() === 'pages';
            });
        });
    }

    /** @test */
    function it_gets_entries_from_multiple_collections()
    {
        $entries = $this->repo->whereInCollection(['alphabetical', 'blog']);

        $this->assertInstanceOf(EntryCollection::class, $entries);
        $this->assertCount(5, $entries);
        $this->assertEveryItemIsInstanceOf(Entry::class, $entries);
        $this->assertEquals([
            'alphabetical-alpha',
            'alphabetical-bravo',
            'alphabetical-zulu',
            'blog-christmas',
            'blog-fourth-of-july',
        ], $entries->map->id()->sort()->values()->all());
    }

    /** @test */
    function it_gets_entry_by_id()
    {
        $entry = $this->repo->find('alphabetical-bravo');

        $this->assertInstanceOf(Entry::class, $entry);
        $this->assertEquals('Bravo', $entry->get('title'));

        $this->assertNull($this->repo->find('unknown'));
    }

    /** @test */
    function it_gets_entry_by_uri()
    {
        $entry = $this->repo->findByUri('/alphabetical/bravo');

        $this->assertInstanceOf(Entry::class, $entry);
        $this->assertEquals('Bravo', $entry->get('title'));

        $this->assertNull($this->repo->findByUri('/unknown'));
    }

    /** @test */
    function it_gets_entry_by_structure_uri()
    {
        $entry = $this->repo->findByUri('/about/board/directors');

        $this->assertInstanceOf(Entry::class, $entry);
        $this->assertEquals('pages-directors', $entry->id());
        $this->assertEquals('Directors', $entry->get('title'));
    }
}

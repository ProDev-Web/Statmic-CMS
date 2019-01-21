<?php

namespace Tests\Stache\Repositories;

use Tests\TestCase;
use Statamic\Stache\Stache;
use Statamic\API\Collection;
use Statamic\API\Entry as EntryAPI;
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
        $this->directory = __DIR__.'/../__fixtures__/content/collections';
        $stache->registerStores([
            (new CollectionsStore($stache, app('files')))->directory($this->directory),
            (new EntriesStore($stache, app('files')))->directory($this->directory),
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
                return $item->collectionHandle() === 'alphabetical';
            });
        });

        tap($this->repo->whereCollection('blog'), function ($entries) {
            $this->assertInstanceOf(EntryCollection::class, $entries);
            $this->assertCount(2, $entries);
            $this->assertEveryItemIsInstanceOf(Entry::class, $entries);
            $this->assertEveryItem($entries, function ($item) {
                return $item->collectionHandle() === 'blog';
            });
        });

        tap($this->repo->whereCollection('numeric'), function ($entries) {
            $this->assertInstanceOf(EntryCollection::class, $entries);
            $this->assertCount(3, $entries);
            $this->assertEveryItemIsInstanceOf(Entry::class, $entries);
            $this->assertEveryItem($entries, function ($item) {
                return $item->collectionHandle() === 'numeric';
            });
        });

        tap($this->repo->whereCollection('pages'), function ($entries) {
            $this->assertInstanceOf(EntryCollection::class, $entries);
            $this->assertCount(6, $entries);
            $this->assertEveryItemIsInstanceOf(Entry::class, $entries);
            $this->assertEveryItem($entries, function ($item) {
                return $item->collectionHandle() === 'pages';
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
    function it_gets_entry_by_slug()
    {
        $entry = $this->repo->findBySlug('bravo', 'alphabetical');

        $this->assertInstanceOf(Entry::class, $entry);
        $this->assertEquals('Bravo', $entry->get('title'));

        $this->assertNull($this->repo->findBySlug('unknown-slug', 'alphabetical'));
        $this->assertNull($this->repo->findBySlug('bravo', 'unknown-collection'));
        $this->assertNull($this->repo->findBySlug('unknown-slug', 'unknown-collection'));
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

    /** @test */
    function it_saves_an_entry_to_the_stache_and_to_a_file()
    {
        $entry = EntryAPI::make()
            ->id('test-blog-entry')
            ->collection(Collection::whereHandle('blog'))
            ->in('en', function ($loc) {
                $loc
                    ->slug('test')
                    ->published(false)
                    ->order('2017-07-04')
                    ->data(['foo' => 'bar']);
            });

        $this->assertCount(14, $this->repo->all());
        $this->assertNull($this->repo->find('test-blog-entry'));

        $this->repo->save($entry);

        $this->assertCount(15, $this->repo->all());
        $this->assertNotNull($item = $this->repo->find('test-blog-entry'));
        $this->assertArraySubset(['foo' => 'bar'], $item->data());
        $this->assertFileExists($path = $this->directory.'/blog/2017-07-04.test.md');
        @unlink($path);
    }
}

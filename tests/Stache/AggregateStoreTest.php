<?php

namespace Tests\Stache;

use Tests\TestCase;
use Statamic\Stache\Stache;
use Illuminate\Support\Facades\Cache;
use Statamic\Stache\Stores\BasicStore;
use Statamic\Stache\Stores\AggregateStore;
use Statamic\Stache\Stores\ChildStore;

class AggregateStoreTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $stache = (new Stache)
            ->sites(['en', 'fr'])
            ->keys(['test/data']);

        $this->app->instance(Stache::class, $stache);

        $this->store = new TestAggregateStore($stache, app('files'));
    }

    /** @test */
    function it_gets_and_sets_child_stores()
    {
        $this->assertEquals([], $this->store->stores()->all());

        $childOne = $this->store->store('one');
        $childTwo = $this->store->store('two');

        $this->assertInstanceOf(ChildStore::class, $childOne);
        $this->assertEquals(['one' => $childOne, 'two' => $childTwo], $this->store->stores()->all());
    }

    /** @test */
    function it_sets_paths_in_all_child_stores()
    {
        $this->assertEquals([], $this->store->store('a')->getPaths()->all());
        $this->assertEquals([], $this->store->store('b')->getPaths()->all());

        $return = $this->store->setPaths($paths = [
            'a::one' => 'one.md',
            'b::two' => 'two.md'
        ]);

        $this->assertEquals($this->store, $return);
        $this->assertEquals(['one' => 'one.md'], $this->store->store('a')->getPaths()->all());
        $this->assertEquals(['two' => 'two.md'], $this->store->store('b')->getPaths()->all());
    }

    /** @test */
    function it_sets_specific_path_in_child_store()
    {
        $this->assertNull($this->store->store('a')->getPath('one'));

        $return = $this->store->setPath('a::one', 'one.md');

        $this->assertEquals($this->store, $return);
        $this->assertEquals('one.md', $this->store->store('a')->getPath('one'));
    }

    /** @test */
    function it_is_loaded_if_all_child_stores_are_loaded()
    {
        $this->store->store('one');
        $this->store->store('two');
        $this->assertFalse($this->store->isLoaded());

        $this->store->store('one')->load();
        $this->assertFalse($this->store->isLoaded());

        $this->store->store('two')->load();
        $this->assertTrue($this->store->isLoaded());
    }

    /** @test */
    function it_loads_all_child_stores()
    {
        $this->store->store('one');
        $this->store->store('two');
        $this->assertFalse($this->store->isLoaded());
        $this->assertFalse($this->store->store('one')->isLoaded());
        $this->assertFalse($this->store->store('two')->isLoaded());

        $this->store->load();

        $this->assertTrue($this->store->isLoaded());
        $this->assertTrue($this->store->store('one')->isLoaded());
        $this->assertTrue($this->store->store('two')->isLoaded());
    }

    /** @test */
    function it_marks_all_child_stores_as_loaded()
    {
        $this->store->store('one');
        $this->store->store('two');
        $this->assertFalse($this->store->isLoaded());
        $this->assertFalse($this->store->store('one')->isLoaded());
        $this->assertFalse($this->store->store('two')->isLoaded());

        $return = $this->store->markAsLoaded();

        $this->assertEquals($this->store, $return);
        $this->assertTrue($this->store->isLoaded());
        $this->assertTrue($this->store->store('one')->isLoaded());
        $this->assertTrue($this->store->store('two')->isLoaded());
    }

    /** @test */
    function it_gets_all_items()
    {
        Cache::shouldReceive('get')->with('stache::items/test::one')->andReturn($items = [
            '123' => ['title' => 'Store One Item One'],
            '456' => ['title' => 'Store One Item Two'],
        ]);
        Cache::shouldReceive('get')->with('stache::items/test::two')->andReturn($items = [
            '789' => ['title' => 'Store Two Item One'],
            '101' => ['title' => 'Store Two Item Two'],
        ]);

        $this->store->store('one')
            ->setItem('123', ['title' => 'Store One Item One'])
            ->setItem('456', ['title' => 'Store One Item Two']);

        $this->store->store('two')
            ->setItem('789', ['title' => 'Store Two Item One'])
            ->setItem('101', ['title' => 'Store Two Item Two']);

        $this->assertEquals([
            'one' => [
                '123' => ['title' => 'Store One Item One'],
                '456' => ['title' => 'Store One Item Two'],
            ],
            'two' => [
                '789' => ['title' => 'Store Two Item One'],
                '101' => ['title' => 'Store Two Item Two'],
            ]
        ], $this->store->getItems()->toArray());
    }

    /** @test */
    function it_sets_child_items()
    {
        $this->assertEquals([], $this->store->store('one')->getItemsWithoutLoading()->all());

        $this->store->setItem('one::123', ['title' => 'Store One Item One']);
        $this->store->setItem('one::456', ['title' => 'Store One Item Two']);
        $this->store->setItem('two::789', ['title' => 'Store Two Item One']);
        $return = $this->store->setItem('two::101', ['title' => 'Store Two Item Two']);

        $this->assertEquals([
            'one' => [
                '123' => ['title' => 'Store One Item One'],
                '456' => ['title' => 'Store One Item Two'],
            ],
            'two' => [
                '789' => ['title' => 'Store Two Item One'],
                '101' => ['title' => 'Store Two Item Two'],
            ]
        ], $this->store->getItemsWithoutLoading()->toArray());

        $this->assertEquals($this->store, $return);
    }

    /** @test */
    function it_gets_and_sets_a_uri_for_a_child_stores_site()
    {
        $this->assertNull($this->store->getSiteUri('en', 'one::123'));

        $return = $this->store->setSiteUri('en', 'one::123', '/one');

        $this->assertEquals('/one', $this->store->getSiteUri('en', 'one::123'));
        $this->assertEquals($this->store, $return);
    }

    /** @test */
    function inserting_an_item_will_set_the_item_and_path_and_uris_in_each_child_store()
    {
        $objectForStoreOne = new class {
            public function id() { return '123'; }
            public function path() { return '/path/to/object'; }
            public function uri() { return '/the/uri'; }
        };
        $objectForStoreTwo = new class {
            public function id() { return '321'; }
            public function path() { return '/path/to/object/in/store/two'; }
            public function uri() { return '/the/uri/in/store/two'; }
        };
        // Inserting an object with an id method should use that as the key if no double colon is provided in the argument.
        $return = $this->store->insert($objectForStoreOne, 'one');
        $this->store->insert($objectForStoreOne, 'one::456');
        $this->store->insert($objectForStoreTwo, 'two');
        $this->store->insert($objectForStoreTwo, 'two::654');

        // Inserting an item with the key and path parameters will use those
        $this->store->insert(['title' => 'Item title'], 'one::789', '/the/path');
        $this->store->insert(['title' => 'Item title in store two'], 'two::987', '/the/path/in/store/two');

        $this->assertEquals($this->store, $return);
        $this->assertEquals([
            'one' => [
                '123' => $objectForStoreOne,
                '456' => $objectForStoreOne,
                '789' => ['title' => 'Item title'],
            ],
            'two' => [
                '321' => $objectForStoreTwo,
                '654' => $objectForStoreTwo,
                '987' => ['title' => 'Item title in store two'],
            ],
        ], $this->store->getItemsWithoutLoading()->toArray());
        $this->assertEquals([
            '123' => '/path/to/object',
            '456' => '/path/to/object',
            '789' => '/the/path',
        ], $this->store->store('one')->getPaths()->all());
        $this->assertEquals([
            '321' => '/path/to/object/in/store/two',
            '654' => '/path/to/object/in/store/two',
            '987' => '/the/path/in/store/two',
        ], $this->store->store('two')->getPaths()->all());
        $this->assertEquals([
            'en' => [
                '123' => '/the/uri',
                '456' => '/the/uri',
            ],
            'fr' => [
                '123' => '/the/uri',
                '456' => '/the/uri',
            ]
        ], $this->store->store('one')->getUris()->toArray());
        $this->assertEquals([
            'en' => [
                '321' => '/the/uri/in/store/two',
                '654' => '/the/uri/in/store/two',
            ],
            'fr' => [
                '321' => '/the/uri/in/store/two',
                '654' => '/the/uri/in/store/two',
            ]
        ], $this->store->store('two')->getUris()->toArray());
    }

    /** @test */
    function it_can_perform_an_action_for_each_child_stores_site()
    {
        $arguments = [];
        $this->assertNull($this->store->store('one')->getSiteUri('en', '123'));
        $this->assertNull($this->store->store('one')->getSiteUri('fr', '123'));

        $return = $this->store->forEachSite(function ($site, $store) use (&$arguments) {
            $arguments[] = [$site, $store];
            $store->setSiteUri($site, 'one::123', '/url-in-' . $site);
        });

        $this->assertEquals([['en', $this->store], ['fr', $this->store]], $arguments);
        $this->assertEquals('/url-in-en', $this->store->getSiteUri('en', 'one::123'));
        $this->assertEquals('/url-in-fr', $this->store->getSiteUri('fr', 'one::123'));
        $this->assertEquals($this->store, $return);
    }

    /** @test */
    function it_caches_items_and_meta_data()
    {
        $this->store->setPath('one::1', '/path/to/one.txt');
        $this->store->setSiteUri('en', 'one::1', '/one');
        $this->store->setItem('one::1', new class {
            public function toCacheableArray() {
                return 'converted using toCacheableArray';
            }
        });

        $this->store->setPath('one::2', '/path/to/two.txt');
        $this->store->setSiteUri('en', 'one::2', '/two');
        $this->store->setSiteUri('fr', 'one::2', '/deux');
        $this->store->setItem('one::2', ['item' => 'two']);

        $this->store->setPath('two::3', '/path/to/three.txt');
        $this->store->setSiteUri('en', 'two::3', '/three');
        $this->store->setSiteUri('fr', 'two::3', '/trois');
        $this->store->setItem('two::3', ['item' => 'three']);

        Cache::shouldReceive('forever')->with('stache::meta/test-keys', ['one', 'two']);

        Cache::shouldReceive('forever')->once()->with('stache::items/test::one', [
            '1' => 'converted using toCacheableArray',
            '2' => ['item' => 'two'],
        ]);
        Cache::shouldReceive('forever')->once()->with('stache::meta/test::one', [
            'paths' => [
                '1' => '/path/to/one.txt',
                '2' => '/path/to/two.txt',
            ],
            'uris' => [
                'en' => [
                    '1' => '/one',
                    '2' => '/two'
                ],
                'fr' => [
                    '2' => '/deux'
                ]
            ]
        ]);

        Cache::shouldReceive('forever')->once()->with('stache::items/test::two', [
            '3' => ['item' => 'three']
        ]);
        Cache::shouldReceive('forever')->once()->with('stache::meta/test::two', [
            'paths' => [
                '3' => '/path/to/three.txt',
            ],
            'uris' => [
                'en' => [
                    '3' => '/three',
                ],
                'fr' => [
                    '3' => '/trois'
                ]
            ]
        ]);

        $this->store->cache();
    }

    /** @test */
    function gets_meta_data_from_cache_in_a_format_suitable_for_collection_mapWithKeys_method()
    {
        $this->store->store('one');
        $this->store->store('two');
        Cache::shouldReceive('get')->with('stache::meta/test-keys')->once()->andReturn(['one', 'two']);
        Cache::shouldReceive('get')->with('stache::meta/test::one')->once()->andReturn('first child stores cache');
        Cache::shouldReceive('get')->with('stache::meta/test::two')->once()->andReturn('second child stores cache');

        $this->assertEquals([
            'test::one' => 'first child stores cache',
            'test::two' => 'second child stores cache'
        ], $this->store->getMetaFromCache());
    }

    /** @test */
    function it_gets_a_map_of_ids_to_the_stores()
    {
        $this->store->setPaths([
            'one::123' => '/path/to/one.md',
            'two::456' => '/path/to/two.md'
        ]);

        $this->assertEquals([
            '123' => 'test::one',
            '456' => 'test::two',
        ], $this->store->getIdMap()->all());
    }

    /** @test */
    function it_gets_an_id_from_a_uri()
    {
        $this->store->store('one')->setUris([
            'en' => $enUris = ['123' => '/one', '456' => '/two'],
            'fr' => $frUris = ['123' => '/un', '456' => '/deux'],
        ]);
        $this->store->store('two')->setUris([
            'en' => $enUris = ['789' => '/three', '101' => '/four'],
            'fr' => $frUris = ['789' => '/tres', '101' => '/cuatro'],
        ]);

        $this->assertEquals('123', $this->store->getIdFromUri('/one'));
        $this->assertEquals('456', $this->store->getIdFromUri('/two'));
        $this->assertEquals('789', $this->store->getIdFromUri('/three'));
        $this->assertEquals('101', $this->store->getIdFromUri('/four'));
    }

    /** @test */
    function it_checks_if_updated()
    {
        $one = $this->store->store('one');
        $two = $this->store->store('two');
        $this->assertFalse($this->store->isUpdated());

        $one->markAsUpdated();

        $this->assertTrue($this->store->isUpdated());
    }
}

class TestAggregateStore extends AggregateStore
{
    public function key()
    {
        return 'test';
    }
}

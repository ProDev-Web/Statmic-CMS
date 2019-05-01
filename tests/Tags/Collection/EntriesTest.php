<?php

namespace Tests\Tags\Collection;

use Statamic\API;
use Tests\TestCase;
use Illuminate\Support\Carbon;
use Statamic\Query\Scopes\Scope;
use Statamic\Tags\Collection\Entries;
use Tests\PreventSavingStacheItemsToDisk;

class EntriesTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    function setUp(): void
    {
        parent::setUp();

        $this->collection = API\Collection::make('test')->save();

        app('statamic.scopes')[PostType::handle()] = PostType::class;
        app('statamic.scopes')[PostAnimal::handle()] = PostAnimal::class;
    }

    protected function makeEntry()
    {
        $entry = API\Entry::make()->collection($this->collection);

        return $entry->makeAndAddLocalization('en', function ($loc) { });
    }

    protected function getEntries($params = [])
    {
        $params['from'] = 'test';

        return (new Entries($params))->get();
    }

    /** @test */
    function it_gets_entries_in_a_collection()
    {
        $this->assertCount(0, $this->getEntries());

        $this->makeEntry()->save();

        $this->assertCount(1, $this->getEntries());
    }

    /** @test */
    function it_gets_paginated_entries_in_a_collection()
    {
        $this->makeEntry()->save();
        $this->makeEntry()->save();
        $this->makeEntry()->save();
        $this->makeEntry()->save();
        $this->makeEntry()->save();

        $this->assertCount(5, $this->getEntries());
        $this->assertCount(3, $this->getEntries(['paginate' => 3]));
        $this->assertCount(4, $this->getEntries(['paginate' => true, 'limit' => 4])); // v2 style
        $this->assertCount(3, $this->getEntries(['paginate' => 3, 'limit' => 4])); // precedence
        $this->assertCount(5, $this->getEntries(['paginate' => true])); // ignore if no perPage set
    }

    /** @test */
    function it_gets_localized_site_entries_in_a_collection()
    {
        $this->withoutEvents();

        $this->collection->sites(['en', 'fr'])->save();

        $this->makeEntry()->set('title', 'One')->save();
        $this->makeEntry()->set('title', 'Two')->save();
        $this->makeEntry()->set('title', 'Three')->save();

        $entry = API\Entry::make()->collection($this->collection);
        $entry->makeAndAddLocalization('fr', function ($loc) { })->set('title', 'Quatre')->save();

        $entry = API\Entry::make()->collection($this->collection);
        $entry->makeAndAddLocalization('fr', function ($loc) { })->set('title', 'Cinq')->save();

        $this->assertCount(5, $this->getEntries());

        // TODO: Come back and finish these assertions when we finish multi-site propagation stuff...
        // $this->assertCount(3, $this->getEntries(['site' => 'en']));
        // $this->assertCount(2, $this->getEntries(['site' => 'fr']));
    }

    /** @test */
    function it_limits_entries_with_offset()
    {
        $this->makeEntry()->set('title', 'One')->save();
        $this->makeEntry()->set('title', 'Two')->save();
        $this->makeEntry()->set('title', 'Three')->save();
        $this->makeEntry()->set('title', 'Four')->save();
        $this->makeEntry()->set('title', 'Five')->save();

        $this->assertCount(5, $this->getEntries());

        $this->assertEquals(
            ['One', 'Two', 'Three'],
            $this->getEntries(['limit' => 3])->map->get('title')->values()->all()
        );

        $this->assertEquals(
            ['Two', 'Three', 'Four'],
            $this->getEntries(['limit' => 3, 'offset' => 1])->map->get('title')->values()->all()
        );
    }

    /** @test */
    function it_filters_by_publish_status()
    {
        $this->makeEntry()->published(true)->save();
        $this->makeEntry()->published(true)->save();
        $this->makeEntry()->published(false)->save();

        $this->assertCount(2, $this->getEntries());
        $this->assertCount(2, $this->getEntries(['show_unpublished' => false]));
        $this->assertCount(3, $this->getEntries(['show_unpublished' => true]));
        $this->assertCount(2, $this->getEntries(['show_published' => true]));
        $this->assertCount(0, $this->getEntries(['show_published' => false]));
        $this->assertCount(1, $this->getEntries(['show_published' => false, 'show_unpublished' => true]));
    }

    /** @test */
    function it_filters_by_future_and_past()
    {
        Carbon::setTestNow(Carbon::parse('2019-03-10 13:00'));

        $this->makeEntry()->date('2019-03-09')->save(); // definitely in past
        $this->makeEntry()->date('2019-03-10')->save(); // today
        $this->makeEntry()->date('2019-03-10-1259')->save(); // today, but before "now"
        $this->makeEntry()->date('2019-03-10-1300')->save(); // today, and also "now"
        $this->makeEntry()->date('2019-03-10-1301')->save(); // today, but after "now"
        $this->makeEntry()->date('2019-03-11')->save(); // definitely in future

        // Default date behaviors.
        $this->collection->dated(true)->save();
        $this->assertCount(6, $this->getEntries());
        $this->assertCount(3, $this->getEntries(['show_future' => false]));
        $this->assertCount(6, $this->getEntries(['show_future' => true]));
        $this->assertCount(6, $this->getEntries(['show_past' => true]));
        $this->assertCount(2, $this->getEntries(['show_past' => false]));
        $this->assertCount(2, $this->getEntries(['show_past' => false, 'show_future' => true]));

        // Only future
        $this->collection->dated(true)->futureDateBehavior('public')->pastDateBehavior('unlisted')->save();
        $this->assertCount(2, $this->getEntries());
        $this->assertCount(0, $this->getEntries(['show_future' => false]));
        $this->assertCount(2, $this->getEntries(['show_future' => true]));
        $this->assertCount(6, $this->getEntries(['show_past' => true]));
        $this->assertCount(2, $this->getEntries(['show_past' => false]));
        $this->assertCount(2, $this->getEntries(['show_past' => false, 'show_future' => true]));

        $this->collection->dated(true)->futureDateBehavior('public')->pastDateBehavior('private')->save();
        $this->assertCount(2, $this->getEntries());
        $this->assertCount(0, $this->getEntries(['show_future' => false]));
        $this->assertCount(2, $this->getEntries(['show_future' => true]));
        $this->assertCount(6, $this->getEntries(['show_past' => true]));
        $this->assertCount(2, $this->getEntries(['show_past' => false]));
        $this->assertCount(2, $this->getEntries(['show_past' => false, 'show_future' => true]));

        // Only past
        $this->collection->dated(true)->futureDateBehavior('unlisted')->pastDateBehavior('public')->save();
        $this->assertCount(3, $this->getEntries());
        $this->assertCount(3, $this->getEntries(['show_future' => false]));
        $this->assertCount(6, $this->getEntries(['show_future' => true]));
        $this->assertCount(3, $this->getEntries(['show_past' => true]));
        $this->assertCount(0, $this->getEntries(['show_past' => false]));
        $this->assertCount(2, $this->getEntries(['show_past' => false, 'show_future' => true]));

        $this->collection->dated(true)->futureDateBehavior('private')->pastDateBehavior('public')->save();
        $this->assertCount(3, $this->getEntries());
        $this->assertCount(3, $this->getEntries(['show_future' => false]));
        $this->assertCount(6, $this->getEntries(['show_future' => true]));
        $this->assertCount(3, $this->getEntries(['show_past' => true]));
        $this->assertCount(0, $this->getEntries(['show_past' => false]));
        $this->assertCount(2, $this->getEntries(['show_past' => false, 'show_future' => true]));
    }

    /** @test */
    function it_filters_by_since_and_until()
    {
        $this->collection->dated(true)->save();
        Carbon::setTestNow(Carbon::parse('2019-03-10 13:00'));

        $this->makeEntry()->date('2019-03-06')->save(); // further in past
        $this->makeEntry()->date('2019-03-09')->save(); // yesterday
        $this->makeEntry()->date('2019-03-10')->save(); // today
        $this->makeEntry()->date('2019-03-10-1259')->save(); // today, but before "now"
        $this->makeEntry()->date('2019-03-10-1300')->save(); // today, and also "now"
        $this->makeEntry()->date('2019-03-10-1301')->save(); // today, but after "now"
        $this->makeEntry()->date('2019-03-11')->save(); // tomorrow
        $this->makeEntry()->date('2019-03-13')->save(); // further in future

        $this->assertCount(8, $this->getEntries(['show_future' => true]));
        $this->assertCount(6, $this->getEntries(['show_future' => true, 'since' => 'yesterday']));
        $this->assertCount(7, $this->getEntries(['show_future' => true, 'since' => '-2 days']));
        $this->assertCount(4, $this->getEntries(['show_future' => true, 'until' => 'now']));
        $this->assertCount(6, $this->getEntries(['show_future' => true, 'until' => 'tomorrow']));
    }

    /** @test */
    function it_filters_by_custom_query_scopes()
    {
        $this->makeEntry()->set('title', 'Cat Stories')->save();
        $this->makeEntry()->set('title', 'Tiger Stories')->save();
        $this->makeEntry()->set('title', 'Tiger Fables')->save();
        $this->makeEntry()->set('title', 'Tiger Tales')->save();

        $this->assertCount(4, $this->getEntries());
        $this->assertCount(2, $this->getEntries(['query' => 'post_type', 'post_type' => 'stories']));
        $this->assertCount(2, $this->getEntries(['filter' => 'post_type', 'post_type' => 'stories']));
        $this->assertCount(3, $this->getEntries(['query' => 'post_animal', 'post_animal' => 'tiger']));
        $this->assertCount(3, $this->getEntries(['filter' => 'post_animal', 'post_animal' => 'tiger']));

        $this->assertCount(1, $this->getEntries([
            'query' => 'post_type|post_animal',
            'post_type' => 'stories',
            'post_animal' => 'tiger'
        ]));
    }

    /** @test */
    function it_sorts_entries()
    {
        $this->collection->dated(true)->save();
        Carbon::setTestNow(Carbon::parse('2019-03-10 13:00'));

        $this->makeEntry()->date('2019-02-06')->set('title', 'Pear')->save();
        $this->makeEntry()->date('2019-02-07')->set('title', 'Apple')->save();
        $this->makeEntry()->date('2019-03-03')->set('title', 'Banana')->save();

        $this->assertEquals(
            ['2019-03-03', '2019-02-07', '2019-02-06'],
            $this->getEntries(['sort' => 'date:desc'])->map->date()->map->format('Y-m-d')->all()
        );

        $this->assertEquals(
            ['Apple', 'Banana', 'Pear'],
            $this->getEntries(['sort' => 'title'])->map->get('title')->all()
        );

        $this->assertEquals(
            ['Pear', 'Banana', 'Apple'],
            $this->getEntries(['order_by' => 'title:desc'])->map->get('title')->all()
        );
    }
}

class PostType extends Scope
{
    public function apply($query, $params)
    {
        $query->where('title', 'like', "%{$params['post_type']}%");
    }
}

class PostAnimal extends Scope
{
    public function apply($query, $params)
    {
        $query->where('title', 'like', "%{$params['post_animal']}%");
    }
}

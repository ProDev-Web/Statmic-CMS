<?php

namespace Tests\Tags\Collection;

use Statamic\API;
use Tests\TestCase;
use Statamic\Tags\Collection\Entries;
use Tests\PreventSavingStacheItemsToDisk;
use Illuminate\Support\Carbon;

class EntriesTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    function setUp(): void
    {
        parent::setUp();
        $this->collection = API\Collection::make('test')->save();
    }

    protected function makeEntry()
    {
        $entry = API\Entry::make()->collection($this->collection);
        return $entry->makeAndAddLocalization('en', function ($loc) { });
    }

    protected function getEntries($params = [])
    {
        return (new Entries('test', $params))->get();
    }

    /** @test */
    function it_gets_entries_in_a_collection()
    {
        $this->assertCount(0, $this->getEntries());

        $this->makeEntry()->save();

        $this->assertCount(1, $this->getEntries());
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
        $this->collection->order('date')->save();
        Carbon::setTestNow(Carbon::parse('2019-03-10 13:00'));

        $this->makeEntry()->order('2019-03-09')->save(); // definitely in past
        $this->makeEntry()->order('2019-03-10')->save(); // today
        $this->makeEntry()->order('2019-03-10-1259')->save(); // today, but before "now"
        $this->makeEntry()->order('2019-03-10-1300')->save(); // today, and also "now"
        $this->makeEntry()->order('2019-03-10-1301')->save(); // today, but after "now"
        $this->makeEntry()->order('2019-03-11')->save(); // definitely in future

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
        $this->collection->order('date')->save();
        Carbon::setTestNow(Carbon::parse('2019-03-10 13:00'));

        $this->makeEntry()->order('2019-03-06')->save(); // further in past
        $this->makeEntry()->order('2019-03-09')->save(); // yesterday
        $this->makeEntry()->order('2019-03-10')->save(); // today
        $this->makeEntry()->order('2019-03-10-1259')->save(); // today, but before "now"
        $this->makeEntry()->order('2019-03-10-1300')->save(); // today, and also "now"
        $this->makeEntry()->order('2019-03-10-1301')->save(); // today, but after "now"
        $this->makeEntry()->order('2019-03-11')->save(); // tomorrow
        $this->makeEntry()->order('2019-03-13')->save(); // further in future

        $this->assertCount(8, $this->getEntries(['show_future' => true]));
        $this->assertCount(6, $this->getEntries(['show_future' => true, 'since' => 'yesterday']));
        $this->assertCount(7, $this->getEntries(['show_future' => true, 'since' => '-2 days']));
        $this->assertCount(4, $this->getEntries(['show_future' => true, 'until' => 'now']));
        $this->assertCount(6, $this->getEntries(['show_future' => true, 'until' => 'tomorrow']));
    }
}

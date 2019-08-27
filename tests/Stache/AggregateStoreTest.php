<?php

namespace Tests\Stache;

use Tests\TestCase;
use Statamic\Stache\Stache;
use Statamic\Stache\Stores\ChildStore;
use Statamic\Stache\Stores\AggregateStore;

class AggregateStoreTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $stache = (new Stache)->sites(['en', 'fr']);

        $this->app->instance(Stache::class, $stache);

        $this->store = new TestAggregateStore;
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
}

class TestAggregateStore extends AggregateStore
{
    protected $childStore = TestChildStore::class;

    public function key()
    {
        return 'test';
    }
}

class TestChildStore extends ChildStore
{

}

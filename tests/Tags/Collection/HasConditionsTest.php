<?php

namespace Tests\Tags\Collection;

use Statamic\API;
use Tests\TestCase;
use Statamic\Tags\Collection\Entries;
use Tests\PreventSavingStacheItemsToDisk;
use Illuminate\Support\Carbon;

class HasConditionsTest extends TestCase
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
    function it_filters_by_is_condition()
    {
        $this->makeEntry()->set('title', 'Dog')->save();
        $this->makeEntry()->set('title', 'Cat')->save();
        $this->makeEntry()->set('title', 'Tiger')->save();

        $this->assertCount(3, $this->getEntries());
        $this->assertCount(1, $this->getEntries(['title:is' => 'Dog']));
        $this->assertCount(1, $this->getEntries(['title:equals' => 'Dog']));
    }

    /** @test */
    function it_filters_by_not_condition()
    {
        $this->makeEntry()->set('title', 'Dog')->save();
        $this->makeEntry()->set('title', 'Cat')->save();
        $this->makeEntry()->set('title', 'Tiger')->save();

        $this->assertCount(3, $this->getEntries());
        $this->assertCount(2, $this->getEntries(['title:not' => 'Dog']));
        $this->assertCount(2, $this->getEntries(['title:isnt' => 'Dog']));
        $this->assertCount(2, $this->getEntries(['title:aint' => 'Dog']));
        $this->assertCount(2, $this->getEntries(['title:¯\\_(ツ)_/¯' => 'Dog']));
    }

    /** @test */
    function it_filters_by_contains_condition()
    {
        $this->makeEntry()->set('title', 'Dog Stories')->save();
        $this->makeEntry()->set('title', 'Cat Fables')->save();
        $this->makeEntry()->set('title', 'Tiger Tales')->save();

        $this->assertCount(3, $this->getEntries());
        $this->assertCount(1, $this->getEntries(['title:contains' => 'Sto']));
    }

    /** @test */
    function it_filters_by_doesnt_contain_condition()
    {
        $this->makeEntry()->set('title', 'Dog Stories')->save();
        $this->makeEntry()->set('title', 'Cat Fables')->save();
        $this->makeEntry()->set('title', 'Tiger Tales')->save();

        $this->assertCount(3, $this->getEntries());
        $this->assertCount(2, $this->getEntries(['title:doesnt_contain' => 'Sto']));
    }

    /** @test */
    function it_filters_by_starts_with_condition()
    {
        $this->makeEntry()->set('title', 'Dog Stories')->save();
        $this->makeEntry()->set('title', 'Cat Fables')->save();
        $this->makeEntry()->set('title', 'Tiger Tales')->save();

        $this->assertCount(3, $this->getEntries());
        $this->assertCount(0, $this->getEntries(['title:starts_with' => 'Sto']));
        $this->assertCount(0, $this->getEntries(['title:begins_with' => 'Sto']));
        $this->assertCount(1, $this->getEntries(['title:starts_with' => 'Dog']));
        $this->assertCount(1, $this->getEntries(['title:begins_with' => 'Dog']));
    }

    /** @test */
    function it_filters_by_doesnt_start_with_condition()
    {
        $this->makeEntry()->set('title', 'Dog Stories')->save();
        $this->makeEntry()->set('title', 'Cat Fables')->save();
        $this->makeEntry()->set('title', 'Tiger Tales')->save();

        $this->assertCount(3, $this->getEntries());
        $this->assertCount(3, $this->getEntries(['title:doesnt_start_with' => 'Sto']));
        $this->assertCount(3, $this->getEntries(['title:doesnt_begin_with' => 'Sto']));
        $this->assertCount(2, $this->getEntries(['title:doesnt_start_with' => 'Dog']));
        $this->assertCount(2, $this->getEntries(['title:doesnt_begin_with' => 'Dog']));
    }

    /** @test */
    function it_filters_by_ends_with_condition()
    {
        $this->makeEntry()->set('title', 'Dog Stories')->save();
        $this->makeEntry()->set('title', 'Cat Fables')->save();
        $this->makeEntry()->set('title', 'Tiger Tales')->save();

        $this->assertCount(3, $this->getEntries());
        $this->assertCount(0, $this->getEntries(['title:ends_with' => 'Sto']));
        $this->assertCount(1, $this->getEntries(['title:ends_with' => 'Stories']));
    }

    /** @test */
    function it_filters_by_doesnt_end_with_condition()
    {
        $this->makeEntry()->set('title', 'Dog Stories')->save();
        $this->makeEntry()->set('title', 'Cat Fables')->save();
        $this->makeEntry()->set('title', 'Tiger Tales')->save();

        $this->assertCount(3, $this->getEntries());
        $this->assertCount(3, $this->getEntries(['title:doesnt_end_with' => 'Sto']));
        $this->assertCount(2, $this->getEntries(['title:doesnt_end_with' => 'Stories']));
    }

    /** @test */
    function it_filters_by_greater_than_condition()
    {
        $this->makeEntry()->set('age', 11)->save();
        $this->makeEntry()->set('age', '11')->save();
        $this->makeEntry()->set('age', 21)->save();
        $this->makeEntry()->set('age', '21')->save();
        $this->makeEntry()->set('age', 24)->save();
        $this->makeEntry()->set('age', '24')->save();

        $this->assertCount(6, $this->getEntries());
        $this->assertCount(4, $this->getEntries(['age:greater_than' => 18]));
        $this->assertCount(4, $this->getEntries(['age:gt' => 18]));
        $this->assertCount(4, $this->getEntries(['age:greater_than' => '18']));
        $this->assertCount(4, $this->getEntries(['age:gt' => '18']));
    }

    /** @test */
    function it_filters_by_less_than_condition()
    {
        $this->makeEntry()->set('age', 11)->save();
        $this->makeEntry()->set('age', '11')->save();
        $this->makeEntry()->set('age', 21)->save();
        $this->makeEntry()->set('age', '21')->save();
        $this->makeEntry()->set('age', 24)->save();
        $this->makeEntry()->set('age', '24')->save();

        $this->assertCount(6, $this->getEntries());
        $this->assertCount(2, $this->getEntries(['age:less_than' => 18]));
        $this->assertCount(2, $this->getEntries(['age:lt' => 18]));
        $this->assertCount(2, $this->getEntries(['age:less_than' => '18']));
        $this->assertCount(2, $this->getEntries(['age:lt' => '18']));
    }

    /** @test */
    function it_filters_by_greater_than_or_equal_to_condition()
    {
        $this->makeEntry()->set('age', 11)->save();
        $this->makeEntry()->set('age', '11')->save();
        $this->makeEntry()->set('age', 21)->save();
        $this->makeEntry()->set('age', '21')->save();
        $this->makeEntry()->set('age', 24)->save();
        $this->makeEntry()->set('age', '24')->save();

        $this->assertCount(6, $this->getEntries());
        $this->assertCount(4, $this->getEntries(['age:greater_than_or_equal_to' => 21]));
        $this->assertCount(4, $this->getEntries(['age:gte' => 21]));
        $this->assertCount(4, $this->getEntries(['age:greater_than_or_equal_to' => '21']));
        $this->assertCount(4, $this->getEntries(['age:gte' => '21']));
    }

    /** @test */
    function it_filters_by_less_than_or_equal_to_condition()
    {
        $this->makeEntry()->set('age', 11)->save();
        $this->makeEntry()->set('age', '11')->save();
        $this->makeEntry()->set('age', 21)->save();
        $this->makeEntry()->set('age', '21')->save();
        $this->makeEntry()->set('age', 24)->save();
        $this->makeEntry()->set('age', '24')->save();

        $this->assertCount(6, $this->getEntries());
        $this->assertCount(4, $this->getEntries(['age:less_than_or_equal_to' => 21]));
        $this->assertCount(4, $this->getEntries(['age:lte' => 21]));
        $this->assertCount(4, $this->getEntries(['age:less_than_or_equal_to' => '21']));
        $this->assertCount(4, $this->getEntries(['age:lte' => '21']));
    }

    /** @test */
    function it_filters_by_regex_condition()
    {
        $this->makeEntry()->set('title', 'Dog Stories')->save();
        $this->makeEntry()->set('title', 'Cat Fables')->save();
        $this->makeEntry()->set('title', 'Tiger Tales')->save();
        $this->makeEntry()->set('title', 'Why I love my cat')->save();
        $this->makeEntry()->set('title', 'Paw Poetry')->save();

        $this->assertCount(5, $this->getEntries());
        $this->assertCount(2, $this->getEntries(['title:matches' => 'cat']));
        $this->assertCount(2, $this->getEntries(['title:match' => 'cat']));
        $this->assertCount(2, $this->getEntries(['title:regex' => 'cat']));
        $this->assertCount(1, $this->getEntries(['title:matches' => '^cat']));
        $this->assertCount(1, $this->getEntries(['title:match' => '^cat']));
        $this->assertCount(1, $this->getEntries(['title:regex' => '^cat']));
        $this->assertCount(1, $this->getEntries(['title:matches' => 'c.t$']));
        $this->assertCount(1, $this->getEntries(['title:match' => 'c.t$']));
        $this->assertCount(1, $this->getEntries(['title:regex' => 'c.t$']));
    }

    /** @test */
    function it_filters_by_not_regex_condition()
    {
        $this->makeEntry()->set('title', 'Dog Stories')->save();
        $this->makeEntry()->set('title', 'Cat Fables')->save();
        $this->makeEntry()->set('title', 'Tiger Tales')->save();
        $this->makeEntry()->set('title', 'Why I love my cat')->save();
        $this->makeEntry()->set('title', 'Paw Poetry')->save();

        $this->assertCount(5, $this->getEntries());
        $this->assertCount(3, $this->getEntries(['title:doesnt_match' => 'cat']));
        $this->assertCount(4, $this->getEntries(['title:doesnt_match' => '^cat']));
        $this->assertCount(4, $this->getEntries(['title:doesnt_match' => 'c.t$']));
    }

    /** @test */
    function it_filters_by_is_alpha_condition()
    {
        $this->makeEntry()->set('title', 'Post')->save();
        $this->makeEntry()->set('title', 'Post Two')->save();
        $this->makeEntry()->set('title', 'It\'s a post')->save();
        $this->makeEntry()->set('title', 'Post1')->save();
        $this->makeEntry()->set('title', 'Post 2')->save();

        $this->assertCount(5, $this->getEntries());
        $this->assertCount(1, $this->getEntries(['title:is_alpha' => true]));
        $this->assertCount(4, $this->getEntries(['title:is_alpha' => false]));
    }

    /** @test */
    function it_filters_by_is_alpha_numeric_condition()
    {
        $this->makeEntry()->set('title', 'Post')->save();
        $this->makeEntry()->set('title', 'Post Two')->save();
        $this->makeEntry()->set('title', 'It\'s a post')->save();
        $this->makeEntry()->set('title', 'Post1')->save();
        $this->makeEntry()->set('title', 'Post 2')->save();

        $this->assertCount(5, $this->getEntries());
        $this->assertCount(2, $this->getEntries(['title:is_alpha_numeric' => true]));
        $this->assertCount(3, $this->getEntries(['title:is_alpha_numeric' => false]));
    }

    /** @test */
    function it_filters_by_is_numeric_condition()
    {
        $this->makeEntry()->set('title', 'Post')->save();
        $this->makeEntry()->set('title', 'Post Two')->save();
        $this->makeEntry()->set('title', 'It\'s a post')->save();
        $this->makeEntry()->set('title', '1.2.3')->save();
        $this->makeEntry()->set('title', '1 2')->save();
        $this->makeEntry()->set('title', '1')->save(); // integer
        $this->makeEntry()->set('title', '1.2')->save(); // float
        $this->makeEntry()->set('title', '.2')->save(); // float

        $this->assertCount(8, $this->getEntries());
        $this->assertCount(3, $this->getEntries(['title:is_numeric' => true]));
        $this->assertCount(5, $this->getEntries(['title:is_numeric' => false]));
    }

    /** @test */
    function it_filters_by_is_url_condition()
    {
        $this->makeEntry()->set('url', 'https://domain.tld')->save();
        $this->makeEntry()->set('url', 'http://domain.tld')->save();
        $this->makeEntry()->set('url', 'https://www.domain.tld/uri/segment.extension?param=one&two=true')->save();
        $this->makeEntry()->set('url', 'http://www.domain.tld/uri/segment.extension?param=one&two=true')->save();
        $this->makeEntry()->set('url', 'http://')->save();
        $this->makeEntry()->set('url', ' http://')->save();
        $this->makeEntry()->set('url', 'http://domain with space.tld')->save();
        $this->makeEntry()->set('url', 'domain-only.tld')->save();
        $this->makeEntry()->set('url', 'definitely not a url')->save();

        $this->assertCount(9, $this->getEntries());
        $this->assertCount(4, $this->getEntries(['url:is_url' => true]));
        $this->assertCount(5, $this->getEntries(['url:is_url' => false]));

        $this->getEntries(['url:is_url' => true])->map->get('url')->each(function ($url) {
            $this->assertContains('domain.tld', $url);
        });
    }
}

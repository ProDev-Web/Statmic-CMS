<?php

namespace Tests\Tags;

use Statamic\API\File;
use Tests\TestCase;
use Statamic\API\Parse;
use Tests\FakesViews;

class PartialTagsTest extends TestCase
{
    use FakesViews;

    public function setUp()
    {
        parent::setUp();
        $this->withFakeViews();
    }

    private function tag($tag)
    {
        return Parse::template($tag, []);
    }

    protected function partialTag($src, $params = '')
    {
        return $this->tag("{{ partial:{$src} $params }}");
    }

    /** @test */
    function gets_partials_from_views_directory()
    {
        $this->viewShouldReturnRaw('mypartial', 'the partial content');

        $this->assertEquals('the partial content', $this->partialTag('mypartial'));
    }

    /** @test */
    function gets_partials_from_partials_directory()
    {
        $this->viewShouldReturnRaw('partials.sub.mypartial', 'the partial content');

        $this->assertEquals('the partial content', $this->partialTag('sub.mypartial'));
    }

    /** @test */
    function gets_partials_with_underscore_prefix()
    {
        $this->viewShouldReturnRaw('sub._mypartial', 'the partial content');

        $this->assertEquals('the partial content', $this->partialTag('sub.mypartial'));
    }

    /** @test */
    function partials_can_contain_front_matter()
    {
        $this->viewShouldReturnRaw('mypartial', "---\nfoo: bar\n---\nthe partial content with {{ foo }}");

        $this->assertEquals(
            'the partial content with bar',
            $this->partialTag('mypartial')
        );
    }

    /** @test */
    function partials_can_pass_data_through_params()
    {
        $this->viewShouldReturnRaw('mypartial', "the partial content with {{ foo }}");

        $this->assertEquals(
            'the partial content with bar',
            $this->partialTag('mypartial', 'foo="bar"')
        );
    }

    /** @test */
    function parameter_will_override_partial_front_matter()
    {
        $this->viewShouldReturnRaw('mypartial', "---\nfoo: bar\n---\nthe partial content with {{ foo }}");

        $this->assertEquals(
            'the partial content with baz',
            $this->partialTag('mypartial', 'foo="baz"')
        );
    }
}

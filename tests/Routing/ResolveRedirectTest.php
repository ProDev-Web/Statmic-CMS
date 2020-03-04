<?php

namespace Tests\Routing;

use Mockery;
use PHPUnit\Framework\TestCase;
use Statamic\Structures\Page;
use Statamic\Structures\Pages;
use Statamic\Routing\ResolveRedirect;

class ResolveRedirectTest extends TestCase
{
    /** @test */
    public function it_resolves_standard_redirects()
    {
        $resolver = new ResolveRedirect;

        $this->assertEquals('http://test.com', $resolver('http://test.com'));
        $this->assertEquals('https://test.com', $resolver('https://test.com'));
        $this->assertEquals('/test', $resolver('/test'));
        $this->assertEquals('test', $resolver('test'));
        $this->assertEquals('404', $resolver('404'));
    }

    /** @test */
    function it_cant_resolve_a_first_child_without_a_parent()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot resolve a page\'s child redirect without providing a page.');

        $resolver = new ResolveRedirect;

        $this->assertEquals('/page/child', $resolver('@child'));
    }

    /** @test */
    function it_cannot_resolve_a_first_child_redirect_if_the_parent_is_not_a_page()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot resolve a page\'s child redirect without providing a page.');

        $resolver = new ResolveRedirect;

        $this->assertEquals('/page/child', $resolver('@child', 'not a page object'));
    }

    /** @test */
    function it_resolves_first_child()
    {
        $resolver = new ResolveRedirect;

        $child = Mockery::mock(Page::class);
        $child->shouldReceive('url')->andReturn('/parent/first-child');

        $children = Mockery::mock(Pages::class);
        $children->shouldReceive('all')->andReturn(collect([$child]));

        $parent = Mockery::mock(Page::class);
        $parent->shouldReceive('pages')->andReturn($children);

        $this->assertEquals('/parent/first-child', $resolver('@child', $parent));
    }

    /** @test */
    function a_parent_without_a_child_resolves_to_a_404()
    {
        $resolver = new ResolveRedirect;

        $parent = Mockery::mock(Page::class);
        $parent->shouldReceive('pages')->andReturn(collect([]));

        $this->assertEquals('404', $resolver('@child', $parent));
    }
}

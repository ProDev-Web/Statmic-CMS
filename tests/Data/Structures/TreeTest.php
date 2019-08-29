<?php

namespace Tests\Data\Structures;

use Tests\TestCase;
use Tests\UnlinksPaths;
use Statamic\API\Entry;
use Statamic\API\Collection;
use Statamic\Data\Structures\Page;
use Statamic\Data\Structures\Tree;
use Statamic\Data\Structures\Pages;
use Statamic\Data\Structures\Structure;
use Tests\PreventSavingStacheItemsToDisk;
use Statamic\API\Structure as StructureAPI;

class TreeTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;
    use UnlinksPaths;

    public function setUp(): void
    {
        parent::setUp();

        $stache = $this->app->make('stache');
        $dir = __DIR__.'/../../Stache/__fixtures__';
        $stache->store('collections')->directory($dir . '/content/collections');
        $stache->store('entries')->directory($dir . '/content/collections');
    }

    /** @test */
    function it_gets_the_route_from_the_collection()
    {
        $collection = tap(Collection::make('test-collection')
            ->structure('test-structure')
            ->route('the-uri/{slug}')
        )->save();

        $this->unlinkAfter($collection->path());

        $structure = (new Structure)->handle('test-structure');
        $tree = (new Tree)->structure($structure);

        $this->assertEquals('the-uri/{slug}', $tree->route());
    }

    /** @test */
    function a_structure_without_a_collection_has_no_route()
    {
        $structure = (new Structure)->handle('test-structure');
        $tree = (new Tree)->structure($structure);

        $this->assertNull($tree->route());
    }

    /** @test */
    function it_gets_the_parent()
    {
        $tree = $this->tree();

        $parent = $tree->parent();

        $this->assertInstanceOf(Page::class, $parent);
        $this->assertEquals(Entry::find('pages-home'), $parent->entry());
    }

    /** @test */
    function it_gets_the_child_pages_including_the_parent_by_default()
    {
        $pages = $this->tree()->pages();

        $this->assertInstanceOf(Pages::class, $pages);
        $this->assertCount(3, $pages->all());
    }

    /** @test */
    function it_gets_the_child_pages_without_the_parent()
    {
        $pages = $this->tree()->withoutParent()->pages();

        $this->assertInstanceOf(Pages::class, $pages);
        $this->assertCount(2, $pages->all());
    }

    /** @test */
    function it_gets_a_page_by_key()
    {
        $page = $this->tree()->page('pages-directors');

        $this->assertEquals('Directors', $page->title());
    }

    protected function tree()
    {
        return (new Tree)
            ->structure(new Structure)
            ->root('pages-home')
            ->tree([
                [
                    'entry' => 'pages-about',
                    'children' => [
                        [
                            'entry' => 'pages-board',
                            'children' => [
                                [
                                    'entry' => 'pages-directors'
                                ]
                            ]
                        ]
                    ],
                ],
                [
                    'entry' => 'pages-blog'
                ],
            ]);
    }
}

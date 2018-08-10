<?php

namespace Tests\Stache\Repositories;

use Tests\TestCase;
use Statamic\Stache\Stache;
use Illuminate\Support\Collection;
use Statamic\Stache\Stores\StructuresStore;
use Statamic\Contracts\Data\Structures\Structure;
use Statamic\Stache\Repositories\StructureRepository;

class StuctureRepositoryTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $stache = (new Stache)->sites(['en']);
        $this->directory = __DIR__.'/../__fixtures__/content/structures';
        $stache->registerStore((new StructuresStore($stache, app('files')))->directory($this->directory));

        $this->repo = new StructureRepository($stache);
    }

    /** @test */
    function it_gets_all_structures()
    {
        $structures = $this->repo->all();

        $this->assertInstanceOf(Collection::class, $structures);
        $this->assertCount(2, $structures);
        $this->assertEveryItemIsInstanceOf(Structure::class, $structures);

        $ordered = $structures->sortBy->handle()->values();
        $this->assertEquals(['footer', 'pages'], $ordered->map->handle()->all());
        $this->assertEquals(['Footer', 'Pages'], $ordered->map->title()->all());
    }

    /** @test */
    function it_gets_a_structure_by_handle()
    {
        tap($this->repo->findByHandle('pages'), function ($structure) {
            $this->assertInstanceOf(Structure::class, $structure);
            $this->assertEquals('pages', $structure->handle());
            $this->assertEquals('Pages', $structure->title());
        });

        tap($this->repo->findByHandle('footer'), function ($structure) {
            $this->assertInstanceOf(Structure::class, $structure);
            $this->assertEquals('footer', $structure->handle());
            $this->assertEquals('Footer', $structure->title());
        });

        $this->assertNull($this->repo->findByHandle('unknown'));
    }

    /** @test */
    function it_saves_a_structure_to_the_stache_and_to_a_file()
    {
        $structure = (new \Statamic\Data\Structures\Structure)->handle('new')->data(['foo' => 'bar']);
        $this->assertNull($this->repo->findByHandle('new'));

        $this->repo->save($structure);

        $this->assertNotNull($item = $this->repo->findByHandle('new'));
        $this->assertEquals(['foo' => 'bar'], $item->data());
        $this->assertFileExists($this->directory.'/new.yaml');
        @unlink($this->directory.'/new.yaml');
    }
}

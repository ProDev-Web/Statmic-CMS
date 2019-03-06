<?php

namespace Tests\Stache\Stores;

use Statamic\API;
use Tests\TestCase;
use Statamic\API\File;
use Statamic\Assets\Asset;
use Statamic\Stache\Stache;
use Illuminate\Filesystem\Filesystem;
use Facades\Statamic\Stache\Traverser;
use Statamic\Contracts\Assets\AssetContainer;
use Statamic\Stache\Stores\AssetContainersStore;

class AssetContainersStoreTest extends TestCase
{
    function setUp(): void
    {
        parent::setUp();

        mkdir($this->tempDir = __DIR__.'/tmp');

        $stache = (new Stache)->sites(['en']);
        $this->store = (new AssetContainersStore($stache, app('files')))->directory($this->tempDir);
    }

    function tearDown(): void
    {
        parent::tearDown();
        (new Filesystem)->deleteDirectory($this->tempDir);
    }

    /** @test */
    function it_gets_yaml_files()
    {
        touch($this->tempDir.'/one.yaml', 1234567890);
        touch($this->tempDir.'/two.yaml', 1234567890);
        touch($this->tempDir.'/three.txt', 1234567890);
        mkdir($this->tempDir.'/subdirectory');
        touch($this->tempDir.'/subdirectory/nested-one.yaml', 1234567890);
        touch($this->tempDir.'/subdirectory/nested-two.yaml', 1234567890);

        $files = Traverser::traverse($this->store);

        $this->assertEquals([
            $this->tempDir.'/one.yaml' => 1234567890,
            $this->tempDir.'/two.yaml' => 1234567890,
            $this->tempDir.'/subdirectory/nested-one.yaml' => 1234567890,
            $this->tempDir.'/subdirectory/nested-two.yaml' => 1234567890,
        ], $files->all());

        // Sanity check. Make sure the file is there but wasn't included.
        $this->assertTrue(file_exists($this->tempDir.'/three.txt'));
    }

    /** @test */
    function it_makes_asset_container_instances_from_cache()
    {
        $container = API\AssetContainer::create();

        $items = $this->store->getItemsFromCache(collect([
            'one' => [
                'title' => 'One',
                'disk' => 'local',
                'blueprint' => 'one',
                'assets' => [
                    'foo.txt' => ['foo' => 'foo'],
                    'bar.txt' => ['foo' => 'bar'],
                ]
            ],
            'two' => [
                'title' => 'Two',
                'disk' => 'local',
                'blueprint' => 'two',
                'assets' => [
                    'baz.txt' => ['foo' => 'baz'],
                    'qux.txt' => ['foo' => 'qux'],
                ]
            ]
        ]));

        $this->assertCount(2, $items);
        $this->assertEveryItemIsInstanceOf(AssetContainer::class, $items);
    }

    /** @test */
    function it_makes_asset_container_instances_from_files()
    {
        config(['filesystems.disks.test' => ['driver' => 'local', 'root' => __DIR__.'/../../Assets/__fixtures__/container']]);

        API\Blueprint::shouldReceive('find')
            ->with('test')->once()
            ->andReturn($blueprint = new \Statamic\Fields\Blueprint);

$contents = <<<EOL
disk: test
title: Example
blueprint: test
EOL;
        $item = $this->store->createItemFromFile($this->tempDir.'/example.yaml', $contents);

        $this->assertInstanceOf(AssetContainer::class, $item);
        $this->assertEquals(File::disk('test'), $item->disk());
        $this->assertEquals('example', $item->handle());
        $this->assertEquals('Example', $item->title());
        $this->assertEquals($blueprint, $item->blueprint());
        tap($item->assets(), function ($assets) {
            $this->assertEveryItemIsInstanceOf(Asset::class, $assets);
            $this->assertEquals([
                'a.txt' => ['title' => 'File A'],
                'b.txt' => ['title' => 'File B'],
                'nested/nested-a.txt' => ['title' => 'Nested File A'],
                'nested/nested-b.txt' => ['title' => 'Nested File B'],
                'nested/double-nested/double-nested-a.txt' => ['title' => 'Double Nested File A'],
                'nested/double-nested/double-nested-b.txt' => ['title' => 'Double Nested File B'],
            ], $assets->keyBy->path()->map->data()->all());
        });
    }

    /** @test */
    function it_uses_the_filename_as_the_item_key()
    {
        $this->assertEquals(
            'test',
            $this->store->getItemKey('irrelevant', '/path/to/test.yaml')
        );
    }

    /** @test */
    function it_saves_to_disk()
    {
        API\Stache::shouldReceive('store')
            ->with('asset-containers')
            ->andReturn($this->store);

        $container = API\AssetContainer::make('new')
            ->title('New Container')
            ->blueprint('foo');

        $this->store->save($container);

        $expected = <<<EOT
title: 'New Container'
blueprint: foo

EOT;
        $this->assertStringEqualsFile($this->tempDir.'/new.yaml', $expected);

        $this->store->save($container);

        $expected = <<<EOT
title: 'New Container'
blueprint: foo

EOT;
        $this->assertStringEqualsFile($this->tempDir.'/new.yaml', $expected);
    }
}

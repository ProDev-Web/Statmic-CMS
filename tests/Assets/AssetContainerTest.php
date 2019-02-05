<?php

namespace Tests\Assets;

use Statamic\API;
use Tests\TestCase;
use Statamic\Assets\Asset;
use Statamic\Fields\Blueprint;
use Illuminate\Support\Collection;
use Statamic\Assets\AssetContainer;
use Illuminate\Support\Facades\Storage;
use Statamic\Filesystem\FlysystemAdapter;
use Tests\PreventSavingStacheItemsToDisk;
use Statamic\Contracts\Assets\AssetFolder;
use Facades\Statamic\Fields\BlueprintRepository;
use Statamic\Contracts\Assets\Asset as AssetContract;

class AssetContainerTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    function it_gets_and_sets_the_id()
    {
        $container = new AssetContainer;
        $this->assertNull($container->id());

        $return = $container->id('123');

        $this->assertEquals($container, $return);
        $this->assertEquals('123', $container->id());
    }

    /** @test */
    function it_gets_and_sets_the_handle()
    {
        $container = new AssetContainer;
        $this->assertNull($container->handle());

        $return = $container->handle('123');

        $this->assertEquals($container, $return);
        $this->assertEquals('123', $container->handle());
    }

    /** @test */
    function it_changes_the_handle_when_changing_the_id()
    {
        // only applies to a file implementation

        $container = (new AssetContainer)->handle('handle');
        $container->id('id');
        $this->assertEquals('id', $container->handle());
    }

    /** @test */
    function it_changes_the_id_when_changing_the_handle()
    {
        // only applies to a file implementation

        $container = (new AssetContainer)->id('id');
        $container->handle('handle');
        $this->assertEquals('handle', $container->id());
    }

    /** @test */
    function it_gets_and_sets_the_disk()
    {
        config(['filesystems.disks.test' => $diskConfig = [
            'driver' => 'local',
            'root' => __DIR__.'/__fixtures__/container',
        ]]);

        $container = new AssetContainer;
        $this->assertNull($container->disk());

        $return = $container->disk('test');

        $this->assertEquals($container, $return);
        $this->assertInstanceOf(FlysystemAdapter::class, $container->disk());
        $this->assertEquals('test', $container->diskHandle());
        $this->assertEquals($diskConfig, $container->diskConfig());
    }

    /** @test */
    function it_gets_and_sets_whether_its_private()
    {
        $container = new AssetContainer;
        $this->assertFalse($container->private());

        $return = $container->private(true);

        $this->assertEquals($container, $return);
        $this->assertTrue($container->private());
        $this->assertFalse($container->accessible());

        $container->private(false);
        $this->assertFalse($container->private());
        $this->assertTrue($container->accessible());
    }

    /** @test */
    function it_gets_and_sets_the_title()
    {
        $container = (new AssetContainer)->handle('main');
        $this->assertEquals('Main', $container->title());

        $return = $container->title('Main Assets');

        $this->assertEquals($container, $return);
        $this->assertEquals('Main Assets', $container->title());
    }

    /** @test */
    function it_gets_and_sets_blueprint()
    {
        config(['statamic.theming.blueprints.asset' => 'default-asset']);

        BlueprintRepository::shouldReceive('find')
            ->with('default-asset')
            ->andReturn($defaultBlueprint = new Blueprint);

        BlueprintRepository::shouldReceive('find')
            ->with('custom')
            ->andReturn($customBlueprint = new Blueprint);

        $container = new AssetContainer;
        $this->assertEquals($defaultBlueprint, $container->blueprint());

        $return = $container->blueprint('custom');

        $this->assertEquals($container, $return);
        $this->assertEquals($customBlueprint, $container->blueprint());
    }

    /** @test */
    function it_saves_the_container_through_the_api()
    {
        API\AssetContainer::spy();

        $container = new AssetContainer;

        $return = $container->save();

        $this->assertEquals($container, $return);
        API\AssetContainer::shouldHaveReceived('save')->with($container)->once();
    }

    /** @test */
    function it_gets_the_path_from_the_stache()
    {
        $container = (new AssetContainer)->handle('test');

        $this->assertEquals($this->fakeStacheDirectory.'/test.yaml', $container->path());
    }

    /** @test */
    function it_gets_all_files_by_default()
    {
        $this->assertEquals([
            'a.txt',
            'b.txt',
            'nested/double-nested/double-nested-a.txt',
            'nested/double-nested/double-nested-b.txt',
            'nested/nested-a.txt',
            'nested/nested-b.txt',
        ], $this->containerWithDisk()->files()->all());
    }

    /** @test */
    function it_gets_files_in_a_folder()
    {
        $this->assertEquals([
            'a.txt',
            'b.txt',
        ], $this->containerWithDisk()->files('/')->all());

        $this->assertEquals([
            'nested/nested-a.txt',
            'nested/nested-b.txt',
        ], $this->containerWithDisk()->files('nested')->all());
    }

    /** @test */
    function it_gets_files_in_a_folder_recursively()
    {
        $this->assertEquals([
            'a.txt',
            'b.txt',
            'nested/double-nested/double-nested-a.txt',
            'nested/double-nested/double-nested-b.txt',
            'nested/nested-a.txt',
            'nested/nested-b.txt',
        ], $this->containerWithDisk()->files('/', true)->all());

        $this->assertEquals([
            'nested/double-nested/double-nested-a.txt',
            'nested/double-nested/double-nested-b.txt',
            'nested/nested-a.txt',
            'nested/nested-b.txt',
        ], $this->containerWithDisk()->files('nested', true)->all());
    }

    /** @test */
    function it_gets_all_folders_by_default()
    {
        $this->assertEquals([
            'nested',
            'nested/double-nested',
        ], $this->containerWithDisk()->folders()->all());
    }

    /** @test */
    function it_gets_folders_in_given_folder()
    {
        $this->assertEquals([
            'nested',
        ], $this->containerWithDisk()->folders('/')->all());

        $this->assertEquals([
            'nested/double-nested',
        ], $this->containerWithDisk()->folders('nested')->all());
    }

    /** @test */
    function it_gets_folders_in_given_folder_recursively()
    {
        $this->assertEquals([
            'nested',
            'nested/double-nested',
        ], $this->containerWithDisk()->folders('/', true)->all());

        $this->assertEquals([
            'nested/double-nested',
        ], $this->containerWithDisk()->folders('nested', true)->all());
    }

    /** @test */
    function it_adds_an_asset_in_memory()
    {
        $container = (new AssetContainer)->handle('test');
        $this->assertCount(0, $container->pendingAssets());

        $return = $container->addAsset($asset = (new Asset)->path('one.txt'));

        $this->assertEquals($container, $return);
        $this->assertEquals($container, $asset->container());
        $this->assertEquals(['one.txt' => $asset], $container->pendingAssets()->all());
    }

    /** @test */
    function it_removes_an_asset_from_memory()
    {
        $container = (new AssetContainer)
            ->handle('test')
            ->addAsset($first = (new Asset)->path('one.txt'))
            ->addAsset($second = (new Asset)->path('two.txt'))
            ->addAsset($third = (new Asset)->path('three.txt'));

        $return = $container->removeAsset($second);

        $this->assertEquals($container, $return);
        $this->assertEquals(['one.txt', 'three.txt'], $container->pendingAssets()->keys()->all());
    }

    /** @test */
    function it_gets_an_asset()
    {
        $asset = $this->containerWithDisk()->asset('a.txt');

        $this->assertInstanceOf(AssetContract::class, $asset);
    }

    /** @test */
    function it_gets_an_asset_with_data()
    {
        $container = $this->containerWithDisk()
            ->addAsset((new Asset)->path($existentPath = 'a.txt')->data(['foo' => 'bar']))
            ->addAsset((new Asset)->path($nonExistentPath = 'non-existent.txt')->data(['foo' => 'bar']));

        tap($container->asset($existentPath), function ($asset) {
            $this->assertInstanceOf(AssetContract::class, $asset);
            $this->assertEquals('bar', $asset->get('foo'));
        });

        $this->assertNull($container->asset($nonExistentPath));
    }

    /** @test */
    function it_gets_all_assets_by_default()
    {
        $assets = $this->containerWithDisk()->assets();

        $this->assertInstanceOf(Collection::class, $assets);
        $this->assertCount(6, $assets);
        $this->assertEveryItemIsInstanceOf(Asset::class, $assets);
        $this->assertEquals([
            'a.txt',
            'b.txt',
            'nested/double-nested/double-nested-a.txt',
            'nested/double-nested/double-nested-b.txt',
            'nested/nested-a.txt',
            'nested/nested-b.txt',
        ], $assets->map->path()->values()->all());
    }

    /** @test */
    function it_gets_assets_in_a_folder()
    {
        tap($this->containerWithDisk()->assets('/'), function ($assets) {
            $this->assertInstanceOf(Collection::class, $assets);
            $this->assertCount(2, $assets);
            $this->assertEveryItemIsInstanceOf(Asset::class, $assets);
        });

        tap($this->containerWithDisk()->assets('nested'), function ($assets) {
            $this->assertInstanceOf(Collection::class, $assets);
            $this->assertCount(2, $assets);
            $this->assertEveryItemIsInstanceOf(Asset::class, $assets);
        });
    }

    function it_gets_assets_in_a_folder_recursively()
    {
        tap($this->containerWithDisk()->assets('/', true), function ($assets) {
            $this->assertInstanceOf(Collection::class, $assets);
            $this->assertCount(6, $assets);
            $this->assertEveryItemIsInstanceOf(Asset::class, $assets);
        });

        tap($this->containerWithDisk()->assets('nested', true), function ($assets) {
            $this->assertInstanceOf(Collection::class, $assets);
            $this->assertCount(4, $assets);
            $this->assertEveryItemIsInstanceOf(Asset::class, $assets);
        });
    }

    /** @test */
    function it_gets_an_asset_folder()
    {
        Storage::fake('test');
        $container = $this->containerWithDisk();

        $folder = $container->assetFolder('foo');

        $this->assertInstanceOf(AssetFolder::class, $folder);
        $this->assertEquals('foo', $folder->title());
        $this->assertEquals('foo', $folder->path());
        $this->assertEquals($container, $folder->container());

        Storage::disk('test')->put('foo/folder.yaml', "title: 'Test Folder'");
        $folder = $container->assetFolder('foo');

        $this->assertEquals('Test Folder', $folder->title());
    }

    private function containerWithDisk()
    {
        config(['filesystems.disks.test' => [
            'driver' => 'local',
            'root' => __DIR__.'/__fixtures__/container',
        ]]);

        return (new AssetContainer)->disk('test');
    }
}

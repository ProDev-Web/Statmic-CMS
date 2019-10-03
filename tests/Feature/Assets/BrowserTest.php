<?php

namespace Tests\Feature\Assets;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class BrowserTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        config(['filesystems.disks.test' => [
            'driver' => 'local',
            'root' => $this->tempDir = __DIR__.'/tmp',
        ]]);
    }

    public function tearDown(): void
    {
        app('files')->deleteDirectory($this->tempDir);
        app('files')->deleteDirectory(storage_path('statamic/dimension-cache'));

        parent::tearDown();
    }

    /** @test */
    function it_redirects_to_the_first_container_from_the_index()
    {
        $this->setTestRoles(['test' => ['access cp', 'view one assets', 'view two assets']]);
        $user = User::make()->assignRole('test')->save();
        $containerOne = AssetContainer::make('one')->save();
        $containerTwo = AssetContainer::make('two')->save();

        $this
            ->actingAs($user)
            ->get(cp_route('assets.browse.index'))
            ->assertRedirect($containerOne->showUrl());
    }

    /** @test */
    function it_redirects_to_the_first_authorized_container_from_the_index()
    {
        $this->setTestRoles(['test' => ['access cp', 'view two assets']]);
        $user = User::make()->assignRole('test')->save();
        $containerOne = AssetContainer::make('one')->save();
        $containerTwo = AssetContainer::make('two')->save();

        $this
            ->actingAs($user)
            ->get(cp_route('assets.browse.index'))
            ->assertRedirect($containerTwo->showUrl());
    }

    /** @test */
    function no_authorized_containers_results_in_a_403_from_the_index()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = User::make()->assignRole('test')->save();
        $containerOne = AssetContainer::make('one')->save();
        $containerTwo = AssetContainer::make('two')->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->get(cp_route('assets.browse.index'))
            ->assertRedirect('/original');
    }

    /** @test */
    function no_containers_at_all_results_in_a_403_from_the_index()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = User::make()->assignRole('test')->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->get(cp_route('assets.browse.index'))
            ->assertRedirect('/original');
    }

    /** @test */
    function no_containers_but_permission_to_create_redirects_to_the_index()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure asset containers']]);
        $user = User::make()->assignRole('test')->save();

        $this
            ->actingAs($user)
            ->get(cp_route('assets.browse.index'))
            ->assertRedirect(cp_route('assets.index'));
    }

    /** @test */
    function it_denies_access()
    {
        $container = AssetContainer::make('test')->save();

        $this
            ->from('/original')
            ->actingAs($this->userWithoutPermission())
            ->get($container->showUrl())
            ->assertRedirect('/original');
    }

    /** @test */
    function it_shows_the_page()
    {
        $container = AssetContainer::make('test')->save();

        $this
            ->actingAs($this->userWithPermission())
            ->get($container->showUrl())
            ->assertSuccessful();
    }

    /** @test */
    function it_lists_assets_in_the_root_folder()
    {
        $this->withoutExceptionHandling();
        $container = AssetContainer::make('test')->disk('test')->save();
        $assetOne = $container
            ->makeAsset('one.txt')
            ->upload(UploadedFile::fake()->create('one.txt'));
        $assetTwo = $container
            ->makeAsset('two.jpg')
            ->upload(UploadedFile::fake()->image('two.jpg'));
        $assetInOtherFolder = $container
            ->makeAsset('subdirectory/other.txt')
            ->upload(UploadedFile::fake()->create('other.txt'));

        $this
            ->actingAs($this->userWithPermission())
            ->getJson('/cp/assets/browse/folders/test/')
            ->assertSuccessful()
            ->assertJsonStructure($this->jsonStructure());
    }

    /** @test */
    function it_lists_assets_in_a_subfolder()
    {
        $container = AssetContainer::make('test')->disk('test')->save();
        $assetOne = $container
            ->makeAsset('nested/subdirectory/one.txt')
            ->upload(UploadedFile::fake()->create('one.txt'));
        $assetTwo = $container
            ->makeAsset('nested/subdirectory/two.jpg')
            ->upload(UploadedFile::fake()->image('two.jpg'));
        $assetInOtherFolder = $container
            ->makeAsset('other.txt')
            ->upload(UploadedFile::fake()->create('other.txt'));

        $this
            ->actingAs($this->userWithPermission())
            ->getJson('/cp/assets/browse/folders/test/nested/subdirectory')
            ->assertSuccessful()
            ->assertJsonStructure($this->jsonStructure());
    }

    /** @test */
    function it_denies_access_to_the_root_folder_without_permission()
    {
        AssetContainer::make('test')->disk('test')->save();

        $this
            ->actingAs($this->userWithoutPermission())
            ->getJson('/cp/assets/browse/folders/test')
            ->assertForbidden();
    }

    /** @test */
    function it_denies_access_to_a_subfolder_without_permission()
    {
        AssetContainer::make('test')->disk('test')->save();

        $this
            ->actingAs($this->userWithoutPermission())
            ->getJson('/cp/assets/browse/folders/test/nested/subdirectory')
            ->assertForbidden();
    }

    /** @test */
    function it_404s_when_requesting_a_folder_in_a_container_that_doesnt_exist()
    {
        $this
            ->actingAs($this->userWithPermission())
            ->getJson('/cp/assets/browse/folders/unknown')
            ->assertNotFound();
    }

    private function userWithPermission()
    {
        $this->setTestRoles(['test' => ['access cp', 'view test assets']]);

        return User::make()->assignRole('test')->save();
    }

    private function userWithoutPermission()
    {
        $this->setTestRoles(['test' => ['access cp']]);

        return User::make()->assignRole('test')->save();
    }

    private function jsonStructure()
    {
        return [
            'meta',
            'links' => ['folder_actions', 'asset_actions'],
            'data' => [
                'assets' => [
                    ['id', 'size_formatted', 'last_modified_relative', 'actions'],
                    ['id', 'size_formatted', 'last_modified_relative', 'actions', 'thumbnail', 'toenail'],
                ],
                'folder' => [
                    'title', 'path', 'parent_path', 'actions', 'folders'
                ]
            ]
        ];
    }
}
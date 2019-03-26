<?php

namespace Tests\Feature\Entries;

use Mockery;
use Tests\TestCase;
use Tests\FakesRoles;
use Statamic\API\User;
use Statamic\API\Entry;
use Statamic\API\Folder;
use Statamic\Fields\Fields;
use Statamic\API\Collection;
use Statamic\Fields\Blueprint;
use Tests\PreventSavingStacheItemsToDisk;
use Facades\Tests\Factories\EntryFactory;
use Facades\Statamic\Fields\BlueprintRepository;

class UpdateEntryTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();
        $this->dir = __DIR__.'/tmp';
        config(['statamic.revisions.path' => $this->dir]);
    }

    public function tearDown(): void
    {
        Folder::delete($this->dir);
        parent::tearDown();
    }

    /** @test */
    function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = User::make()->assignRole('test');

        $entry = EntryFactory::id('1')
            ->slug('test')
            ->collection('blog')
            ->data(['blueprint' => 'test'])
            ->create();

        $this
            ->from('/original')
            ->actingAs($user)
            ->save($entry, [])
            ->assertRedirect('/original')
            ->assertSessionHas('error');
    }

    /** @test */
    function entry_gets_saved()
    {
        $this->setTestBlueprint('test', ['foo' => ['type' => 'text']]);
        $this->setTestRoles(['test' => ['access cp', 'edit blog entries']]);
        $user = User::make()->assignRole('test');

        $entry = EntryFactory::id('1')
            ->slug('test')
            ->collection('blog')
            ->data([
                'blueprint' => 'test',
                'title' => 'Original title',
                'foo' => 'bar',
            ])->create();

        $this
            ->actingAs($user)
            ->save($entry, [
                'title' => 'Updated title',
                'foo' => 'updated foo',
                'slug' => 'updated-slug'
            ])
            ->assertOk();

        $this->assertEquals('test', $entry->slug());
        $this->assertEquals([
            'blueprint' => 'test',
            'title' => 'Original title',
            'foo' => 'bar',
        ], $entry->data());

        $workingCopy = $entry->fromWorkingCopy();
        $this->assertEquals('updated-slug', $workingCopy->slug());
        $this->assertEquals([
            'blueprint' => 'test',
            'title' => 'Updated title',
            'foo' => 'updated foo',
        ], $workingCopy->data());
    }

    /** @test */
    function validation_error_returns_back()
    {
        $this->setTestBlueprint('test', ['foo' => ['type' => 'text', 'validate' => 'required']]);
        $this->setTestRoles(['test' => ['access cp', 'edit blog entries']]);
        $user = User::make()->assignRole('test');

        $entry = EntryFactory::id('1')
            ->slug('test')
            ->collection('blog')
            ->data([
                'blueprint' => 'test',
                'title' => 'Original title',
                'foo' => 'bar',
            ])->create();

        $this
            ->from('/original')
            ->actingAs($user)
            ->save($entry, [
                'title' => 'Updated title',
                'foo' => '',
                'slug' => 'updated-slug'
            ])
            ->assertRedirect('/original')
            ->assertSessionHasErrors('foo');

        $this->assertEquals('test', $entry->slug());
        $this->assertEquals([
            'blueprint' => 'test',
            'title' => 'Original title',
            'foo' => 'bar',
        ], $entry->data());
    }

    private function save($entry, $payload)
    {
        return $this->patch($entry->updateUrl(), $payload);
    }

    private function setTestBlueprint($handle, $fields)
    {
        $fields = collect($fields)->map(function ($field, $handle) {
            return compact('handle', 'field');
        })->all();

        $blueprint = Mockery::mock(Blueprint::class);
        $blueprint->shouldReceive('fields')->andReturn(new Fields($fields));

        $blueprint->shouldReceive('ensureField')->andReturnSelf();
        $blueprint->shouldReceive('ensureFieldPrepended')->andReturnSelf();

        BlueprintRepository::shouldReceive('find')->with('test')->andReturn($blueprint);
    }
}

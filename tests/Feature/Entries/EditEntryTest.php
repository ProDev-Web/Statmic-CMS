<?php

namespace Tests\Feature\Entries;

use Tests\TestCase;
use Tests\FakesRoles;
use Statamic\API\User;
use Statamic\API\Entry;
use Statamic\API\Collection;
use Statamic\Fields\Blueprint;
use Tests\PreventSavingStacheItemsToDisk;
use Facades\Statamic\Fields\BlueprintRepository;

class EditEntryTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    /** @test */
    function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = User::make()->assignRole('test');
        $collection = Collection::create('blog')->save();

        $entry = Entry::create('test')->collection('blog')->date('2017-07-04')->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->get($entry->editUrl())
            ->assertRedirect('/original')
            ->assertSessionHas('error');
    }

    /** @test */
    function it_shows_the_entry_form()
    {
        BlueprintRepository::shouldReceive('find')->with('test')->andReturn(new Blueprint);
        $this->setTestRoles(['test' => ['access cp', 'edit blog entries']]);
        $user = User::make()->assignRole('test');
        $collection = Collection::create('blog')->save();

        $entry = Entry::create('test')->collection('blog')->date('2017-07-04')->with(['blueprint' => 'test'])->save();

        $this
            ->actingAs($user)
            ->get($entry->editUrl())
            ->assertSuccessful()
            ->assertViewHas('entry', $entry)
            ->assertViewHas('readOnly', false);
    }

    /** @test */
    function it_marks_as_read_only_if_you_only_have_view_permission()
    {
        BlueprintRepository::shouldReceive('find')->with('test')->andReturn(new Blueprint);
        $this->setTestRoles(['test' => ['access cp', 'view blog entries']]);
        $user = User::make()->assignRole('test');
        $collection = Collection::create('blog')->save();
        $entry = Entry::create('test')->collection('blog')->date('2017-07-04')->with(['blueprint' => 'test'])->save();

        $this
            ->actingAs($user)
            ->get($entry->editUrl())
            ->assertSuccessful()
            ->assertViewHas('readOnly', true);
    }
}

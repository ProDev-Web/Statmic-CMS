<?php

namespace Tests\Feature\Structures;

use Mockery;
use Statamic\API;
use Tests\TestCase;
use Statamic\Data\Users\User;
use Statamic\Data\Structures\Structure;

class ViewStructureListingTest extends TestCase
{
    /** @test */
    function it_shows_a_list_of_structures()
    {
        API\Structure::shouldReceive('all')->andReturn(collect([
            'foo' => $structureA = $this->createStructure('foo'),
            'bar' => $structureB = $this->createStructure('bar')
        ]));

        $user = API\User::create('test')->with(['super' => true])->get();

        $response = $this
            ->actingAs($user)
            ->get(route('statamic.cp.structures.index'))
            ->assertSuccessful()
            ->assertViewHas('structures', collect([
                'foo' => $structureA,
                'bar' => $structureB
            ]))
            ->assertDontSee('no-results');
    }

    /** @test */
    function it_shows_no_results_when_there_are_no_structures()
    {
        $user = API\User::create('test')->with(['super' => true])->get();

        $response = $this
            ->actingAs($user)
            ->get(route('statamic.cp.structures.index'))
            ->assertSuccessful()
            ->assertViewHas('structures', collect([]))
            ->assertSee('no-results');
    }

    /** @test */
    function it_filters_out_structures_the_user_cannot_access()
    {
        API\Structure::shouldReceive('all')->andReturn(collect([
            'foo' => $structureA = $this->createStructure('foo'),
            'bar' => $structureB = $this->createStructure('bar')
        ]));
        $this->setTestRoles(['test' => ['access cp', 'view bar structure']]);
        $user = API\User::create()->get()->assignRole('test');

        $response = $this
            ->actingAs($user)
            ->get(route('statamic.cp.structures.index'))
            ->assertSuccessful()
            ->assertViewHas('structures', collect([
                'bar' => $structureB
            ]))
            ->assertDontSee('no-results');
    }

    /** @test */
    function it_denies_access_when_there_are_no_permitted_structures()
    {
        API\Structure::shouldReceive('all')->andReturn(collect([
            'foo' => $structureA = $this->createStructure('foo'),
            'bar' => $structureB = $this->createStructure('bar')
        ]));

        $this->setTestRoles(['test' => ['access cp']]);
        $user = API\User::create()->get()->assignRole('test');

        $response = $this
            ->from('/cp/original')
            ->actingAs($user)
            ->get(route('statamic.cp.structures.index'))
            ->assertRedirect('/cp/original');
    }

    private function createStructure($handle)
    {
        return tap(Mockery::mock(Structure::class), function ($s) use ($handle) {
            $s->shouldReceive('title')->andReturn($handle);
            $s->shouldReceive('handle')->andReturn($handle);
            $s->shouldReceive('uris')->andReturn(collect());
            $s->shouldReceive('flattenedPages')->andReturn(collect());
        });
    }

    private function setTestRoles($roles)
    {
        $roles = collect($roles)->map(function ($permissions, $handle) {
            return app(\Statamic\Contracts\Permissions\Role::class)
                ->handle($handle)
                ->addPermission($permissions);
        });

        $fake = new class($roles) extends \Statamic\Permissions\RoleRepository {
            protected $roles;
            public function __construct($roles) {
                $this->roles = $roles;
            }
            public function all(): \Illuminate\Support\Collection {
                return $this->roles;
            }
        };

        app()->instance(\Statamic\Contracts\Permissions\RoleRepository::class, $fake);
    }
}

<?php

namespace Tests\Feature;

use App\Bridge;
use App\Section;
use Tests\TestCase;
use App\Events\BridgeCreated;
use App\Events\BridgeDeleted;
use App\Events\BridgeUpdated;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BridgeInteractionsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function bridgesCanBeListed()
    {
        $this->signIn();

        $user_id = auth()->id();

        $bridges = create(Bridge::class, ['user_id' => $user_id], 5);

        $response = $this->json('GET', route('bridges.index'));

        $responseBridges = $response->json()['bridges'];

        $this->assertEquals(5, count($responseBridges));
        $this->assertSame([1,1,1,1,1], collect($responseBridges)->pluck('user_id')->values()->toArray());
    }

    /** @test */
    public function userCanListOnlyOwnBridges()
    {
        $this->signIn();

        $user_id = auth()->id();

        $bridges = create(Bridge::class, ['user_id' => $user_id], 3);
        create(Bridge::class, ['user_id' => 2], 3);

        $response = $this->json('GET', route('bridges.index'));

        $responseBridges = $response->json()['bridges'];

        $this->assertEquals(3, count($responseBridges));
        $this->assertSame([1,1,1], collect($responseBridges)->pluck('user_id')->values()->toArray());
    }

    /** @test */
    public function aBridgeCanBeShown()
    {
        $this->signIn();
        // $this->withExceptionHandling();

        $user_id = auth()->id();

        $bridge = create(Bridge::class, ['user_id' => $user_id]);

        $response = $this->json('GET', route('bridges.show', ['id' => $bridge->id]));

        $responseBridge = $response->json()['bridge'];

        $response->assertJsonFragment(collect($bridge->toArray())->except('sections')->toArray());
    }

    /** @test */
    public function aNonExistentBridgeThrows404()
    {
        $this->signIn();
        $this->withExceptionHandling();

        $user_id = auth()->id();

        $response = $this->json('GET', route('bridges.show', ['id' => 2]));
        // dd($response->json());
        $response->assertJsonFragment(['error']);
    }

    /** @test */
    public function aNotOwnedBridgeThrows404()
    {
        $this->signIn();
        $this->withExceptionHandling();

        $user_id = auth()->id();

        $bridge = create(Bridge::class, ['user_id' => 2]);

        $response = $this->json('GET', route('bridges.show', ['id' => 1]));

        $response->assertJsonFragment(['error']);
    }

    /** @test */
    public function aBridgeCanBeDeleted()
    {
        $this->signIn();
        $this->expectsEvents(BridgeDeleted::class);

        $bridge = create(Bridge::class, ['user_id' => auth()->id()]);

        $this->assertDatabaseHas('bridges', collect($bridge->toArray())->except('sections')->toArray());

        $response = $this->json('DELETE', route('bridges.destroy', ['id' => 1]));
        $this->assertDatabaseMissing('bridges', $bridge->toArray());
    }

    /** @test */
    public function deletingANonExistentBridgeThrows404()
    {
        $this->signIn();
        $this->withExceptionHandling();

        $user_id = auth()->id();

        $response = $this->json('DELETE', route('bridges.destroy', ['id' => 1]));

        $response->assertJsonFragment(['error']);
    }

    /** @test */
    public function tryingToDeleteANotOwnedBridgeThrows404()
    {
        $this->signIn();
        $this->withExceptionHandling();

        $user_id = auth()->id();

        $bridge = create(Bridge::class, ['user_id' => 2]);

        $response = $this->json('DELETE', route('bridges.destroy', ['id' => 1]));

        $response->assertJsonFragment(['error']);
    }

    /** @test */
    public function aBridgeCanBeUpdated()
    {
        $this->expectsEvents(BridgeUpdated::class);

        $this->signIn();
        $bridge = create(Bridge::class, ['user_id' => auth()->id()]);

        $this->assertDatabaseHas('bridges', collect($bridge->toArray())->except('sections')->toArray());

        $response = $this->json('PATCH', route('bridges.update', $bridge), ['name' => 'foobar']);

        $response->assertJsonFragment(['name' => 'foobar']);
    }

    /** @test */
    public function updatingANonExistentBridgeThrows404()
    {
        $this->signIn();
        $this->withExceptionHandling();

        $user_id = auth()->id();

        $response = $this->json('PATCH', route('bridges.update', 1), ['name' => 'foobar']);

        $response->assertJsonFragment(['error']);
    }

    /** @test */
    public function tryingToUpdateANotOwnedBridgeThrows404()
    {
        $this->signIn();
        $this->withExceptionHandling();

        $user_id = auth()->id();

        $bridge = create(Bridge::class, ['user_id' => 2]);

        $response = $this->json('PATCH', route('bridges.update', 1), ['name' => 'foobar']);

        $response->assertJsonFragment(['error']);
    }

    /** @test */
    public function aBridgeNameCanBeUpdated()
    {
        $this->expectsEvents(BridgeUpdated::class);

        $this->signIn();
        $bridge = create(Bridge::class, ['user_id' => auth()->id()]);


        $response = $this->json('PATCH', route('bridges.updateName', $bridge), ['name' => 'foobar']);

        $response->assertJsonFragment(['name' => 'foobar']);
    }

    /** @test */
    public function updatingNameOfANonExistentBridgeThrows404()
    {
        $this->signIn();
        $this->withExceptionHandling();

        $user_id = auth()->id();

        $response = $this->json('PATCH', route('bridges.updateName', 1), ['name' => 'foobar']);

        $response->assertJsonFragment(['error']);
    }

    /** @test */
    public function tryingToUpdateNameOfANotOwnedBridgeThrows404()
    {
        $this->signIn();
        $this->withExceptionHandling();

        $user_id = auth()->id();

        $bridge = create(Bridge::class, ['user_id' => 2]);

        $response = $this->json('PATCH', route('bridges.updateName', $bridge), ['name' => 'foobar']);

        $response->assertJsonFragment(['error']);
    }

    /** @test */
    public function aBridgeSlugCanBeUpdated()
    {
        $this->expectsEvents(BridgeUpdated::class);

        $this->signIn();
        $bridge = create(Bridge::class, ['user_id' => auth()->id()]);


        $response = $this->json('PATCH', route('bridges.updateSlug', $bridge), ['slug' => 'foobar']);

        $response->assertJsonFragment(['slug' => 'foobar']);
    }

    /** @test */
    public function updatingSlugOfANonExistentBridgeThrows404()
    {
        $this->signIn();
        $this->withExceptionHandling();

        $user_id = auth()->id();

        $response = $this->json('PATCH', route('bridges.updateSlug', 1), ['slug' => 'foobar']);

        $response->assertJsonFragment(['error']);
    }

    /** @test */
    public function tryingToUpdateSlugOfANotOwnedBridgeThrows404()
    {
        $this->signIn();
        $this->withExceptionHandling();

        $user_id = auth()->id();

        $bridge = create(Bridge::class, ['user_id' => 2]);

        $response = $this->json('PATCH', route('bridges.updateSlug', $bridge), ['slug' => 'foobar']);

        $response->assertJsonFragment(['error']);
    }

    /** @test */
    public function aBridgeDuplicateSlugGetsRecalculated()
    {
        $this->signIn();
        $this->expectsEvents(BridgeUpdated::class);

        create(Bridge::class, ['user_id' => auth()->id(), 'slug' => 'foobar']);

        $bridgeToUpdate = create(Bridge::class, ['user_id' => auth()->id()]);

        $response = $this->json('PATCH', route('bridges.updateSlug', $bridgeToUpdate), ['slug' => 'foobar']);

        $this->assertTrue(str_contains($bridgeToUpdate->fresh()->slug, 'foobar-'));
    }

    /** @test */
    public function aBridgeCanBeCreated()
    {
        $this->signIn();
        $this->expectsEvents(BridgeCreated::class);

        $bridgeData = ['name' => 'foobar'];

        $response = $this->json('POST', route('bridges.store'), $bridgeData);

        $response->assertJsonFragment($bridgeData);
    }

    /** @test */
    public function ifABridgeWithTheSameSlugExistsItGetsRecalculated()
    {
        $this->signIn();
        $this->expectsEvents(BridgeCreated::class);


        $bridgeData = ['name' => 'foobar'];



        create(Bridge::class, ['user_id' => auth()->id(), 'slug' => 'foobar']);
        $response = $this->json('POST', route('bridges.store'), $bridgeData);

        $this->assertTrue(str_contains(Bridge::find(2)->slug, 'foobar-'));

        $this->assertInstanceOf('Illuminate\Support\Collection', Bridge::find(2)->sections);
        $this->assertEquals(3, Bridge::find(2)->sections->count());
        $this->assertInstanceOf(Section::class, Bridge::find(2)->sections->first());
    }
}

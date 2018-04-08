<?php

namespace Tests\Feature;

use App\Color;
use App\Bridge;
use Tests\TestCase;
use App\Events\BridgeUpdated;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ColorInteractionsTest extends TestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();

        $this->signIn();

        $this->withExceptionHandling();
    }

    /** @test */
    public function deletingANonExistentColorOrBelongingToANonExistentBridgeThrows404()
    {
        $response = $this->json('DELETE', route('colors.destroy', ['bridgeId' => 1, 'colorId' => 1]));
        $response->assertJsonFragment(['error']);

        create(Bridge::class, ['user_id' => auth()->id()]);

        $response = $this->json('DELETE', route('colors.destroy', ['bridgeId' => 1, 'colorId' => 1]));
        $response->assertJsonFragment(['error']);
    }

    /** @test */
    public function deletingAColorOnANonOwnedBridgeThrows404()
    {
        $bridge = create(Bridge::class, ['user_id' => 2]);

        $color = create(Color::class, ['bridge_id' => $bridge->id]);

        $response = $this->json('DELETE', route('colors.destroy', ['bridgeId' => $bridge->id, 'colorId' => $color->id]));
        $response->assertJsonFragment(['error']);
    }

    /** @test */
    public function aColorCanDeleted()
    {
        $this->expectsEvents(BridgeUpdated::class);

        $bridge = create(Bridge::class, ['user_id' => auth()->id()]);

        $color = create(Color::class, ['bridge_id' => $bridge->id]);

        $this->assertDatabaseHas('colors', $color->toArray());
        $this->assertEquals(1, Color::all()->count());

        $response = $this->json('DELETE', route('colors.destroy', ['bridgeId' => $bridge->id, 'colorId' => $color->id]));

        $this->assertDatabaseMissing('colors', $color->toArray());
        $this->assertEquals(0, Color::all()->count());
    }

    /** @test */
    public function updatingANonExistentColorOrBelongingToANonExistentBridgeThrows404()
    {
        $colorData = make(Color::class)->toArray();

        $response = $this->json('PATCH', route('colors.update', ['bridgeId' => 1, 'colorId' => 1]), $colorData);

        $response->assertJsonFragment(['error']);

        create(Bridge::class, ['user_id' => auth()->id()]);

        $response = $this->json('PATCH', route('colors.update', ['bridgeId' => 1, 'colorId' => 1]), $colorData);
        $response->assertJsonFragment(['error']);
    }

    /** @test */
    public function updatingAColorOnANonOwnedBridgeThrows404()
    {
        $user_id = auth()->id();

        $bridge = create(Bridge::class, ['user_id' => 2]);

        $color = create(Color::class, ['bridge_id' => $bridge->id]);

        $colorData = make(Color::class)->toArray();

        $response = $this->json('PATCH', route('colors.update', ['bridgeId' => $bridge->id, 'colorId' => $color->id]), $colorData);
        $response->assertJsonFragment(['error']);
    }

    /** @test */
    public function aColorCanBeUpdated()
    {
        $this->expectsEvents(BridgeUpdated::class);

        $bridge = create(Bridge::class, ['user_id' => auth()->id()]);

        $color = create(Color::class, ['bridge_id' => $bridge->id]);

        $colorData = make(Color::class)->toArray();

        $response = $this->json('PATCH', route('colors.update', ['bridgeId' => $bridge->id, 'colorId' => $color->id]), $colorData);

        $this->assertSame($color->cmyk, $colorData['cmyk']);
        $this->assertDatabaseHas('colors', $color->fresh()->toArray());
    }

    /** @test */
    public function aColorCanBeStored()
    {
        $this->expectsEvents(BridgeUpdated::class);

        $bridge = create(Bridge::class, ['user_id' => auth()->id()]);

        $colorData = make(Color::class)->toArray();

        $response = $this->json('POST', route('colors.store', ['bridgeId' => $bridge->id]), $colorData);

        $color = Color::first();

        $this->assertSame($color->cmyk, $colorData['cmyk']);
        $this->assertDatabaseHas('colors', $color->fresh()->toArray());
    }

    /** @test */
    public function storingAColorOnANonOwnedBridgeThrows404()
    {
        $bridge = create(Bridge::class, ['user_id' => 2]);

        $colorData = make(Color::class)->toArray();

        $response = $this->json('POST', route('colors.store', ['bridgeId' => $bridge->id]), $colorData);
        $response->assertJsonFragment(['error']);
    }
}

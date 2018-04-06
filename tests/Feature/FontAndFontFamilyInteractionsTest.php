<?php

namespace Tests\Feature;

use App\Font;
use App\Bridge;
use App\FontFamily;
use Tests\TestCase;
use App\FontVariant;
use App\Events\BridgeUpdated;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FontAndFontFamilyInteractionsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function aFontFamilyCanBeSearched()
    {
        $this->signIn();

        $fontFoobar = create(FontFamily::class, ['family' => 'foobar']);
        $fontFoo = create(FontFamily::class, ['family' => 'foo']);
        create(FontFamily::class, [], 5);

        $response = $this->json('GET', route('fonts.search', ['search' => 'foo']));

        $response->assertJsonFragment(['fonts']);

        $this->assertEquals(2, count($response->json()['fonts']));
        $this->assertEquals($response->json()['fonts'][0]['family'], 'foobar');
        $this->assertEquals($response->json()['fonts'][1]['family'], 'foo');
    }

    /** @test */
    public function deletingANonExistentFontOrBelongingToANonExistentBridgeThrows404()
    {
        $this->signIn();
        $this->withExceptionHandling();

        $user_id = auth()->id();

        $response = $this->json('DELETE', route('fonts.destroy', ['bridgeId' => 1, 'fontId' => 1]));
        $response->assertJsonFragment(['error']);

        create(Bridge::class, ['user_id' => auth()->id()]);

        $response = $this->json('DELETE', route('fonts.destroy', ['bridgeId' => 1, 'fontId' => 1]));
        $response->assertJsonFragment(['error']);
    }

    /** @test */
    public function deletingAFontOnANonOwnedBridgeThrows404()
    {
        $this->signIn();
        $this->withExceptionHandling();

        $bridge = create(Bridge::class, ['user_id' => 2]);

        $font = create(Font::class, ['bridge_id' => $bridge->id]);

        $response = $this->json('DELETE', route('fonts.destroy', ['bridgeId' => 1, 'fontId' => 1]));
        $response->assertJsonFragment(['error']);
    }

    /** @test */
    public function aFontCanBeDeleted()
    {
        $this->signIn();
        $this->expectsEvents(BridgeUpdated::class);

        $bridge = create(Bridge::class, ['user_id' => 1]);

        $font = create(Font::class, ['bridge_id' => $bridge->id]);
        $this->assertDatabaseHas('fonts', $font->toArray());

        $response = $this->json('DELETE', route('fonts.destroy', ['bridgeId' => 1, 'fontId' => 1]));
        $this->assertDatabaseMissing('fonts', $font->toArray());
    }

    /** @test */
    public function creatingAFontOnANonExistentBridgeThrows404()
    {
        $this->signIn();
        $this->withExceptionHandling();

        $fontVariant = create(FontVariant::class);

        $user_id = auth()->id();

        $response = $this->json('POST', route('fonts.store', ['bridgeId' => 1, 'font_variant_id' => $fontVariant->id]));
        $response->assertJsonFragment(['error']);
    }

    /** @test */
    public function creatingAFontOnANotOwnedBridgeThrows404()
    {
        $this->signIn();
        $this->withExceptionHandling();

        $fontVariant = create(FontVariant::class);

        $bridge = create(Bridge::class, ['user_id' => 2]);

        $response = $this->json('POST', route('fonts.store', ['bridgeId' => 1, 'font_variant_id' => $fontVariant->id]));

        $response->assertJsonFragment(['error']);
    }

    /** @test */
    public function aFontCanBeCreated()
    {
        $this->signIn();
        $this->expectsEvents(BridgeUpdated::class);

        $fontVariant = create(FontVariant::class, ['link' => 'http://fonts.gstatic.com/s/roboto/v16/7MygqTe2zs9YkP0adA9QQQ.ttf']);

        \Storage::disk('fonts')->delete(md5($fontVariant->link) . '.ttf');
        $bridge = create(Bridge::class, ['user_id' => 1]);

        $response = $this->json('POST', route('fonts.store', ['bridgeId' => 1, 'font_variant_id' => $fontVariant->id]));

        $this->assertEquals(1, Font::all()->count());

        \Storage::disk('fonts')->delete(md5($fontVariant->link) . '.ttf');
    }
}

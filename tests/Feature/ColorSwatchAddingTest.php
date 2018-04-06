<?php

namespace Tests\Feature;

use App\Color;
use App\Bridge;
use App\Section;
use Tests\TestCase;
use App\Events\BridgeUpdated;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ColorSwatchAddingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function loadingSwatchToNonExtistentSwatchThrows404()
    {
        $this->signIn();
        $this->withExceptionHandling();

        $user_id = auth()->id();

        $path = app_path('../tests/utilities/open-color.gpl');
        $file = new UploadedFile($path, 'open-color.gpl', 'image/png', filesize($path), null, true);
        $response = $this->json('POST', route('colors.storeBulk', 1), [
            'swatch' => $file
        ]);
        $response->assertJsonFragment(['error']);
    }

    /** @test */
    public function loadingSwatchToNotOwnedSwatchThrows404()
    {
        $this->signIn();
        $this->withExceptionHandling();


        $bridge = create(Bridge::class, ['user_id' => 2]);

        $path = app_path('../tests/utilities/open-color.gpl');
        $file = new UploadedFile($path, 'open-color.gpl', 'image/png', filesize($path), null, true);
        $response = $this->json('POST', route('colors.storeBulk', 1), [
            'swatch' => $file
        ]);
        $response->assertJsonFragment(['error']);
    }

    /** @test */
    public function userCanLoadSwach()
    {
        $this->signIn();
        $this->expectsEvents(BridgeUpdated::class);
        $bridge = create(Bridge::class, ['user_id' => auth()->id()]);

        $section = create(Section::class, ['bridge_id' => $bridge->id, 'section_type_id' => 1]);
        $path = app_path('../tests/utilities/open-color.gpl');
        $file = new UploadedFile($path, 'open-color.gpl', 'image/png', filesize($path), null, true);

        $response = $this->json('POST', route('colors.storeBulk', $bridge->id), [
            'swatch' => $file
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('colors', [
            'rgb' => '248 249 250',
            'hex' => 'F8F9FA',
            'cmyk' => '1 0 0 2',
        ]);

        $this->assertDatabaseHas('colors', [
            'rgb' => '241 243 245'
        ]);
    }
}

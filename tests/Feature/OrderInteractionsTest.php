<?php

namespace Tests\Feature;

use App\Icon;
use App\Color;
use App\Image;
use App\Bridge;
use App\Section;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderInteractionsTest extends TestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();
        $this->signIn();
        $this->withExceptionHandling();
    }

    /** @test */
    public function colorsInSameSectionCanBeOrdered()
    {
        $bridge = create(Bridge::class, ['user_id' => auth()->id()]);
        $section = create(Section::class, ['section_type_id' => 1]);

        $color = create(Color::class, ['bridge_id' => $bridge->id, 'cmyk' => 199, 'section_id' => $section->id]);
        $colors = create(Color::class, ['bridge_id' => $bridge->id, 'section_id' => $section->id], 4);

        $response = $this->json('POST', route('order.same', ['type' => 'color', 'objectId' => 1, 'newOrder' => 2]));

        $this->assertSame(Color::pluck('order', 'id')->toArray(), [
            1 => 2,
            2 => 1,
            3 => 3,
            4 => 4,
            5 => 5,
        ]);
    }

    /** @test */
    public function iconsInSameSectionCanBeOrdered()
    {
        $bridge = create(Bridge::class, ['user_id' => auth()->id()]);
        $section = create(Section::class, ['section_type_id' => 3]);

        $icon = create(Icon::class, ['bridge_id' => $bridge->id, 'filename' => 'foobar', 'section_id' => $section->id]);
        $icons = create(Icon::class, ['bridge_id' => $bridge->id, 'section_id' => $section->id], 4);

        $response = $this->json('POST', route('order.same', ['type' => 'icon', 'objectId' => 1, 'newOrder' => 2]));

        $this->assertSame(Icon::pluck('order', 'id')->toArray(), [
            1 => 2,
            2 => 1,
            3 => 3,
            4 => 4,
            5 => 5,
        ]);
    }

    /** @test */
    public function imagesInSameSectionCanBeOrdered()
    {
        $bridge = create(Bridge::class, ['user_id' => auth()->id()]);
        $section = create(Section::class, ['section_type_id' => 3]);

        $image = create(Image::class, ['bridge_id' => $bridge->id, 'filename' => 'foobar', 'section_id' => $section->id]);
        $images = create(Image::class, ['bridge_id' => $bridge->id, 'section_id' => $section->id], 4);

        $response = $this->json('POST', route('order.same', ['type' => 'image', 'objectId' => 1, 'newOrder' => 0]));

        $this->assertSame(Image::pluck('order', 'id')->toArray(), [
            1 => 0,
            2 => 2,
            3 => 3,
            4 => 4,
            5 => 5,
        ]);
    }

    /** @test */
    public function updatingAnObjectOfIncorrectTypeThrows404()
    {
        $bridge = create(Bridge::class, ['user_id' => auth()->id()]);
        $section = create(Section::class, ['section_type_id' => 3]);

        $image = create(Image::class, ['bridge_id' => $bridge->id, 'filename' => 'foobar', 'section_id' => $section->id]);
        $images = create(Image::class, ['bridge_id' => $bridge->id, 'section_id' => $section->id], 4);

        $response = $this->json('POST', route('order.same', ['type' => 'foobar', 'objectId' => 1, 'newOrder' => 0]));

        $response->assertJsonFragment(['error']);
    }

    /** @test */
    public function updatingANonExistingObjectThrows404()
    {
        $response = $this->json('POST', route('order.same', ['type' => 'color', 'objectId' => 6, 'newOrder' => 0]));

        $response->assertJsonFragment(['error']);
    }

    /** @test */
    public function updatingAnObjectOnANonOwnedBridgeThrows404()
    {
        $bridge = create(Bridge::class, ['user_id' => 2]);
        $section = create(Section::class, ['section_type_id' => 3]);

        $image = create(Image::class, ['bridge_id' => $bridge->id, 'filename' => 'foobar', 'section_id' => $section->id]);
        $images = create(Image::class, ['bridge_id' => $bridge->id, 'section_id' => $section->id], 4);

        $response = $this->json('POST', route('order.same', ['type' => 'image', 'objectId' => 1, 'newOrder' => 0]));

        $response->assertJsonFragment(['error']);
    }

    /** @test */
    public function colorsCanSwitchSections()
    {
        $bridge = create(Bridge::class, ['user_id' => auth()->id()]);
        $section = create(Section::class, ['bridge_id' => $bridge->id, 'section_type_id' => 1]);

        $color = create(Color::class, ['bridge_id' => $bridge->id, 'cmyk' => 199, 'section_id' => $section->id]);
        $colors = create(Color::class, ['bridge_id' => $bridge->id, 'section_id' => $section->id], 4);

        $sectionNew = create(Section::class, ['bridge_id' => $bridge->id, 'section_type_id' => 1]);

        $response = $this->json('POST', route('order.changed', ['type' => 'color', 'objectId' => 1, 'newSection' => $sectionNew->id]));

        $this->assertSame(Color::where('section_id', $section->id)->pluck('order', 'id')->toArray(), [
            2 => 0,
            3 => 1,
            4 => 2,
            5 => 3,
        ]);

        $this->assertSame(Color::where('section_id', $sectionNew->id)->pluck('order', 'id')->toArray(), [
            1 => 0
        ]);
    }

    /** @test */
    public function iconsCanSwitchSections()
    {
        $bridge = create(Bridge::class, ['user_id' => auth()->id()]);
        $section = create(Section::class, ['bridge_id' => $bridge->id, 'section_type_id' => 1]);

        $icon = create(Icon::class, ['bridge_id' => $bridge->id, 'filename' => 'foobar', 'section_id' => $section->id]);
        $icons = create(Icon::class, ['bridge_id' => $bridge->id, 'section_id' => $section->id], 4);

        $sectionNew = create(Section::class, ['bridge_id' => $bridge->id, 'section_type_id' => 1]);

        $response = $this->json('POST', route('order.changed', ['type' => 'icon', 'objectId' => 1, 'newSection' => $sectionNew->id]));

        $this->assertSame(Icon::where('section_id', $section->id)->pluck('order', 'id')->toArray(), [
            2 => 0,
            3 => 1,
            4 => 2,
            5 => 3,
        ]);

        $this->assertSame(Icon::where('section_id', $sectionNew->id)->pluck('order', 'id')->toArray(), [
            1 => 0
        ]);
    }

    /** @test */
    public function imagesCanSwitchSections()
    {
        $bridge = create(Bridge::class, ['user_id' => auth()->id()]);
        $section = create(Section::class, ['bridge_id' => $bridge->id, 'section_type_id' => 1]);

        $image = create(Image::class, ['bridge_id' => $bridge->id, 'filename' => 'foobar', 'section_id' => $section->id]);
        $images = create(Image::class, ['bridge_id' => $bridge->id, 'section_id' => $section->id], 4);

        $sectionNew = create(Section::class, ['bridge_id' => $bridge->id, 'section_type_id' => 1]);

        $response = $this->json('POST', route('order.changed', ['type' => 'image', 'objectId' => 1, 'newSection' => $sectionNew->id]));

        $this->assertSame(Image::where('section_id', $section->id)->pluck('order', 'id')->toArray(), [
            2 => 0,
            3 => 1,
            4 => 2,
            5 => 3,
        ]);

        $this->assertSame(Image::where('section_id', $sectionNew->id)->pluck('order', 'id')->toArray(), [
            1 => 0
        ]);
    }

    /** @test */
    public function movingAnObjectOfIncorrectTypeThrows404()
    {
        $bridge = create(Bridge::class, ['user_id' => auth()->id()]);
        $section = create(Section::class, ['bridge_id' => $bridge->id, 'section_type_id' => 1]);

        $image = create(Image::class, ['bridge_id' => $bridge->id, 'filename' => 'foobar', 'section_id' => $section->id]);
        $images = create(Image::class, ['bridge_id' => $bridge->id, 'section_id' => $section->id], 4);

        $sectionNew = create(Section::class, ['bridge_id' => $bridge->id, 'section_type_id' => 1]);

        $response = $this->json('POST', route('order.changed', ['type' => 'foobar', 'objectId' => 1, 'newSection' => $sectionNew->id]));

        $response->assertJsonFragment(['error']);
    }

    /** @test */
    public function movingANonExistingObjectThrows404()
    {
        $bridge = create(Bridge::class, ['user_id' => 1]);
        $section = create(Section::class, ['bridge_id' => $bridge->id, 'section_type_id' => 1]);

        $image = create(Image::class, ['bridge_id' => $bridge->id, 'filename' => 'foobar', 'section_id' => $section->id]);
        $images = create(Image::class, ['bridge_id' => $bridge->id, 'section_id' => $section->id], 4);

        $sectionNew = create(Section::class, ['bridge_id' => $bridge->id, 'section_type_id' => 1]);

        $response = $this->json('POST', route('order.changed', ['type' => 'image', 'objectId' => 99, 'newSection' => $sectionNew->id]));


        $response->assertJsonFragment(['error']);
    }

    /** @test */
    public function movingAnObjectOnANonOwnedBridgeThrows404()
    {
        $bridge = create(Bridge::class, ['user_id' => 2]);
        $section = create(Section::class, ['bridge_id' => $bridge->id, 'section_type_id' => 1]);

        $image = create(Image::class, ['bridge_id' => $bridge->id, 'filename' => 'foobar', 'section_id' => $section->id]);
        $images = create(Image::class, ['bridge_id' => $bridge->id, 'section_id' => $section->id], 4);

        $sectionNew = create(Section::class, ['bridge_id' => $bridge->id, 'section_type_id' => 1]);

        $response = $this->json('POST', route('order.changed', ['type' => 'image', 'objectId' => 1, 'newSection' => $sectionNew->id]));


        $response->assertJsonFragment(['error']);
    }
}

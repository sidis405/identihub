<?php

namespace Tests\Feature;

use App\Bridge;
use App\Section;
use Tests\TestCase;
use App\Events\BridgeUpdated;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SectionInteractionsTest extends TestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();
        $this->signIn();
        $this->withExceptionHandling();
    }

    /** @test */
    public function aSectionCaBeCreated()
    {
        $this->expectsEvents(BridgeUpdated::class);
        $bridge = create(Bridge::class, ['user_id' => auth()->id()]);
        $sectionData = make(Section::class, ['bridge_id' => $bridge->id]);

        $this->assertEquals(6, Section::count());
        $response = $this->json('POST', route('sections.store', $bridge), ['section_type' => $sectionData->section_type_id]);

        $this->assertEquals(7, Section::count());
    }

    /** @test */
    public function creatingASectionOnNonOwnedBridgeThrows404()
    {
        $bridge = create(Bridge::class, ['user_id' => 2]);
        $sectionData = make(Section::class, ['bridge_id' => $bridge->id]);

        $this->assertEquals(6, Section::count());
        $response = $this->json('POST', route('sections.store', $bridge), ['section_type' => $sectionData->section_type_id]);

        $response->assertJsonFragment(['error']);
        $this->assertEquals(6, Section::count());
    }


    /** @test */
    public function creatingASectionOnNonExistentBridgeThrows404()
    {
        $sectionData = make(Section::class, ['bridge_id' => 1]);

        $this->assertEquals(3, Section::count());
        $response = $this->json('POST', route('sections.store', ['bridge_id' =>1]), ['section_type' => $sectionData->section_type_id]);

        $response->assertJsonFragment(['error']);
        $this->assertEquals(3, Section::count());
    }

    /** @test */
    public function aSectionCanBeDeleted()
    {
        $this->expectsEvents(BridgeUpdated::class);
        $bridge = create(Bridge::class, ['user_id' => auth()->id()]);
        $section = create(Section::class, ['bridge_id' => $bridge->id]);

        $this->assertEquals(7, Section::count());
        $response = $this->json('DELETE', route('sections.destroy', [$bridge, $section]));

        $this->assertEquals(6, Section::count());
    }

    /** @test */
    public function deletingASectionOnNonOwnedBridgeThrows404()
    {
        $bridge = create(Bridge::class, ['user_id' => 2]);
        $section = create(Section::class, ['bridge_id' => $bridge->id]);

        $this->assertEquals(7, Section::count());
        $response = $this->json('DELETE', route('sections.destroy', [$bridge, $section]));

        $response->assertJsonFragment(['error']);
        $this->assertEquals(7, Section::count());
    }

    /** @test */
    public function deletingANonExistentSectionOrBelongingToANonExistentBridgeThrows404()
    {
        $this->assertEquals(0, Section::count());
        $response = $this->json('DELETE', route('sections.destroy', ['bridge_id' => 1, 'section_id' => 1]));

        $response->assertJsonFragment(['error']);

        $bridge = create(Bridge::class, ['user_id' => 2]);
        $response = $this->json('DELETE', route('sections.destroy', ['bridge_id' => 1, 'section_id' => 1]));

        $this->assertEquals(3, Section::count());
    }

    /** @test */
    public function aSectionTitleCanBeUpdated()
    {
        $this->expectsEvents(BridgeUpdated::class);
        $bridge = create(Bridge::class, ['user_id' => auth()->id()]);
        $section = create(Section::class, ['bridge_id' => $bridge->id]);

        $this->assertEquals(7, Section::count());
        $response = $this->json('PATCH', route('sections.updateTitle', [$bridge, $section]), ['title' => 'foobar']);

        $this->assertDatabaseHas('sections', ['title' => 'foobar']);
    }

    /** @test */
    public function updatingANonExistentSectionTitleOrBelongingToANonExistentBridgeThrows404()
    {
        $response = $this->json('PATCH', route('sections.updateTitle', ['bridge_id' => 1, 'section_id' => 1]), ['title' => 'foobar']);
        $response->assertJsonFragment(['error']);

        $bridge = create(Bridge::class, ['user_id' => auth()->id()]);

        $this->assertEquals(3, Section::count());
        $response = $this->json('PATCH', route('sections.updateTitle', ['bridge_id' => 1, 'section_id' => 99]), ['title' => 'foobar']);

        $response->assertJsonFragment(['error']);
    }

    /** @test */
    public function updatingTitleOfSectionOnANotOwnedBridgeThrows404()
    {
        $bridge = create(Bridge::class, ['user_id' => 2]);
        $section = create(Section::class, ['bridge_id' => $bridge->id]);

        $response = $this->json('PATCH', route('sections.updateTitle', [$bridge, $section]), ['title' => 'foobar']);
        $response->assertJsonFragment(['error']);
    }

    /** @test */
    public function aSectionDescriptionCanBeUpdated()
    {
        $this->expectsEvents(BridgeUpdated::class);
        $bridge = create(Bridge::class, ['user_id' => auth()->id()]);
        $section = create(Section::class, ['bridge_id' => $bridge->id]);

        $this->assertEquals(7, Section::count());
        $response = $this->json('PATCH', route('sections.updateDescription', [$bridge, $section]), ['description' => 'foobar']);

        $this->assertDatabaseHas('sections', ['description' => 'foobar']);
    }

    /** @test */
    public function updatingDescriptionOfSectionOnANotOwnedBridgeThrows404()
    {
        $bridge = create(Bridge::class, ['user_id' => 2]);
        $section = create(Section::class, ['bridge_id' => $bridge->id]);

        $response = $this->json('PATCH', route('sections.updateDescription', [$bridge, $section]), ['description' => 'foobar']);
        $response->assertJsonFragment(['error']);
    }

    /** @test */
    public function updatingANonExistentSectionDescriptionOrBelongingToANonExistentBridgeThrows404()
    {
        $response = $this->json('PATCH', route('sections.updateDescription', ['bridge_id' => 1, 'section_id' => 1]), ['description' => 'foobar']);
        $response->assertJsonFragment(['error']);

        $bridge = create(Bridge::class, ['user_id' => auth()->id()]);

        $this->assertEquals(3, Section::count());
        $response = $this->json('PATCH', route('sections.updateDescription', ['bridge_id' => $bridge->id, 'section_id' => 99]), ['description' => 'foobar']);

        $response->assertJsonFragment(['error']);
    }
}

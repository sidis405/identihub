<?php

namespace Tests\Unit;

use App\Bridge;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BridgeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function aBridgeCanLoadCommonRelations()
    {
        $bridge = create(Bridge::class);

        $bridge = $bridge->loadCommonRelations();

        $this->assertInstanceOf(Bridge::class, $bridge);
        $this->assertInstanceOf('Illuminate\Support\Collection', $bridge->sections);
        $this->assertInstanceOf('Illuminate\Support\Collection', $bridge->icons);
        $this->assertInstanceOf('Illuminate\Support\Collection', $bridge->images);
        $this->assertInstanceOf('Illuminate\Support\Collection', $bridge->fonts);
        $this->assertInstanceOf('Illuminate\Support\Collection', $bridge->colors);
    }
}

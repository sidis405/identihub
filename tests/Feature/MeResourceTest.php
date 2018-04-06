<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MeResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function aUserCanRetrieveOwnProfile()
    {
        $this->signIn();

        $response = $this->json('GET', route('me'));

        $response->assertJsonFragment(
            [
                'name' => auth()->user()->name,
                'email' => auth()->user()->email,
            ]
        );
    }
}

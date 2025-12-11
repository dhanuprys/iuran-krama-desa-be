<?php

namespace Tests\Feature;

use Tests\TestCase;

class VersionTest extends TestCase
{
    /**
     * Test that the meta endpoint returns the correct version structure.
     */
    public function test_meta_endpoint_returns_version()
    {
        $response = $this->getJson('/api/v1/meta');

        $response->assertStatus(200)
            ->assertJson([
                'version' => '1.0.0',
            ]);
    }
}

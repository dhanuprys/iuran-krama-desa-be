<?php

namespace Tests\Feature\Krama;

use App\Models\Banjar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BanjarTest extends TestCase
{
    use RefreshDatabase;

    protected $krama;

    protected function setUp(): void
    {
        parent::setUp();
        // Create a user with krama role
        $this->krama = User::factory()->create(['role' => 'krama']);
    }

    public function test_can_get_banjar_list()
    {
        Banjar::factory()->count(3)->create();

        $response = $this->actingAs($this->krama)->getJson('/api/v1/krama/banjars');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'error',
                'data' => [
                    '*' => ['id', 'name']
                ],
                'meta'
            ])
            ->assertJsonCount(3, 'data');
    }

    public function test_unauthenticated_user_cannot_get_banjar_list()
    {
        $response = $this->getJson('/api/v1/krama/banjars');

        $response->assertStatus(401);
    }
}

<?php

namespace Tests\Feature;

use App\Models\ResidentStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResidentStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_fetch_all_resident_statuses()
    {
        // Seed statuses
        ResidentStatus::factory()->create(['name' => 'Active', 'contribution_amount' => 10000]);
        ResidentStatus::factory()->create(['name' => 'Inactive', 'contribution_amount' => 0]);

        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/v1/resident-statuses');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'contribution_amount'
                    ]
                ]
            ])
            ->assertJsonCount(2, 'data');
    }

    public function test_cannot_fetch_statuses_if_unauthenticated()
    {
        $response = $this->getJson('/api/v1/resident-statuses');

        $response->assertStatus(401);
    }
}

<?php

namespace Tests\Feature\Admin;

use App\Models\ResidentStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResidentStatusTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        // Create an admin user for authentication
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_list_resident_statuses()
    {
        ResidentStatus::factory()->count(5)->create();

        $response = $this->actingAs($this->admin)->getJson('/api/v1/admin/resident-statuses');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [], // Ensure data array exists
            ]);
    }

    public function test_admin_can_create_resident_status()
    {
        $payload = [
            'name' => 'Krama Miu',
            'contribution_amount' => 50000
        ];

        $response = $this->actingAs($this->admin)->postJson('/api/v1/admin/resident-statuses', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Krama Miu',
                    'contribution_amount' => 50000
                ]
            ]);

        $this->assertDatabaseHas('resident_statuses', $payload);
    }

    public function test_admin_can_show_resident_status()
    {
        $status = ResidentStatus::factory()->create();

        $response = $this->actingAs($this->admin)->getJson("/api/v1/admin/resident-statuses/{$status->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $status->id,
                    'name' => $status->name,
                    'contribution_amount' => $status->contribution_amount
                ]
            ]);
    }

    public function test_admin_can_update_resident_status()
    {
        $status = ResidentStatus::factory()->create();
        $payload = [
            'name' => 'Updated Status Name',
            'contribution_amount' => 75000
        ];

        $response = $this->actingAs($this->admin)->putJson("/api/v1/admin/resident-statuses/{$status->id}", $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Updated Status Name',
                    'contribution_amount' => 75000
                ]
            ]);

        $this->assertDatabaseHas('resident_statuses', array_merge(['id' => $status->id], $payload));
    }

    public function test_admin_can_delete_resident_status()
    {
        $status = ResidentStatus::factory()->create();

        $response = $this->actingAs($this->admin)->deleteJson("/api/v1/admin/resident-statuses/{$status->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseMissing('resident_statuses', ['id' => $status->id]);
    }
}

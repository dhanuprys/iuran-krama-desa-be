<?php

namespace Tests\Feature\Admin;

use App\Models\Banjar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BanjarTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        // Create an admin user for authentication
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_list_banjars()
    {
        Banjar::factory()->count(5)->create();

        $response = $this->actingAs($this->admin)->getJson('/api/v1/admin/banjars');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [], // Ensure data array exists
                'meta' => []
            ]);
    }

    public function test_admin_can_create_banjar()
    {
        $payload = [
            'name' => 'Banjar Baru',
            'address' => 'Jl. Banjar Baru No. 1'
        ];

        $response = $this->actingAs($this->admin)->postJson('/api/v1/admin/banjars', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Banjar Baru'
                ]
            ]);

        $this->assertDatabaseHas('banjars', $payload);
    }

    public function test_admin_can_show_banjar()
    {
        $banjar = Banjar::factory()->create();

        $response = $this->actingAs($this->admin)->getJson("/api/v1/admin/banjars/{$banjar->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $banjar->id,
                    'name' => $banjar->name
                ]
            ]);
    }

    public function test_admin_can_update_banjar()
    {
        $banjar = Banjar::factory()->create();
        $payload = ['name' => 'Updated Banjar Name'];

        $response = $this->actingAs($this->admin)->putJson("/api/v1/admin/banjars/{$banjar->id}", $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Updated Banjar Name'
                ]
            ]);

        $this->assertDatabaseHas('banjars', ['id' => $banjar->id, 'name' => 'Updated Banjar Name']);
    }

    public function test_admin_can_delete_banjar()
    {
        $banjar = Banjar::factory()->create();

        $response = $this->actingAs($this->admin)->deleteJson("/api/v1/admin/banjars/{$banjar->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseMissing('banjars', ['id' => $banjar->id]);
    }
}

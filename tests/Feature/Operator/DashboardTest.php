<?php

namespace Tests\Feature\Operator;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_operator_can_access_dashboard()
    {
        $operator = User::factory()->create([
            'role' => 'operator',
        ]);

        $response = $this->actingAs($operator)->getJson('/api/v1/operator/dashboard');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Welcome to Operator Dashboard',
            ]);
    }

    public function test_admin_cannot_access_operator_dashboard()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->getJson('/api/v1/operator/dashboard');

        $response->assertStatus(403);
    }

    public function test_krama_cannot_access_operator_dashboard()
    {
        $krama = User::factory()->create([
            'role' => 'krama',
        ]);

        $response = $this->actingAs($krama)->getJson('/api/v1/operator/dashboard');

        $response->assertStatus(403);
    }

    public function test_guest_cannot_access_operator_dashboard()
    {
        $response = $this->getJson('/api/v1/operator/dashboard');

        $response->assertStatus(401);
    }
}

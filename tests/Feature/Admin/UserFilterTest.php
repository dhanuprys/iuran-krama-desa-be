<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an admin user to perform the requests
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_list_all_users_by_default()
    {
        // Create extra users
        User::factory()->count(3)->create(['role' => 'krama']);
        User::factory()->count(2)->create(['role' => 'admin']);

        // Total users: 1 (this->admin) + 3 krama + 2 admin = 6

        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/admin/users');

        $response->assertStatus(200)
            ->assertJsonCount(6, 'data'); // Should return all 6 users
    }

    public function test_admin_can_filter_users_by_role_krama()
    {
        User::factory()->count(3)->create(['role' => 'krama']);
        User::factory()->count(2)->create(['role' => 'admin']);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/admin/users?role=krama');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data'); // Should return only 3 krama users

        // Verify all returned users have role 'krama'
        foreach ($response->json('data') as $user) {
            $this->assertEquals('krama', $user['role']);
        }
    }

    public function test_admin_can_filter_users_by_role_admin()
    {
        User::factory()->count(3)->create(['role' => 'krama']);
        User::factory()->count(2)->create(['role' => 'admin']);

        // Current auth admin is also an admin, so total admins = 1 (auth) + 2 (created) = 3

        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/admin/users?role=admin');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data'); // Should return 3 admin users

        // Verify all returned users have role 'admin'
        foreach ($response->json('data') as $user) {
            $this->assertEquals('admin', $user['role']);
        }
    }
}

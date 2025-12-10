<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    private $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_list_users()
    {
        User::factory()->count(5)->create(['role' => 'krama']);

        $response = $this->actingAs($this->admin)->getJson('/api/v1/admin/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'pagination',
            ]);
    }

    public function test_admin_can_create_user()
    {
        $data = [
            'name' => 'New Guy',
            'username' => 'newguy',
            'email' => 'newguy@example.com',
            'password' => 'password',
            'role' => 'krama',
            'can_create_resident' => true
        ];

        $response = $this->actingAs($this->admin)->postJson('/api/v1/admin/users', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['username' => 'newguy']);

        $this->assertDatabaseHas('users', ['email' => 'newguy@example.com']);
    }

    public function test_admin_can_show_user()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)
            ->getJson("/api/v1/admin/users/{$user->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'residents' => []
                ]
            ]);
    }

    public function test_admin_can_update_user_details()
    {
        $user = User::factory()->create(['role' => 'krama', 'can_create_resident' => false]);

        $response = $this->actingAs($this->admin)->putJson("/api/v1/admin/users/{$user->id}", [
            'name' => 'Updated Name',
            'can_create_resident' => true
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Name')
            ->assertJsonPath('data.can_create_resident', true);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'can_create_resident' => true,
        ]);
    }

    public function test_admin_can_delete_user()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)->deleteJson("/api/v1/admin/users/{$user->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_admin_cannot_delete_self()
    {
        $response = $this->actingAs($this->admin)->deleteJson("/api/v1/admin/users/{$this->admin->id}");

        $response->assertStatus(403);
    }

    public function test_non_admin_cannot_manage_users()
    {
        $krama = User::factory()->create(['role' => 'krama']);

        $this->actingAs($krama)->getJson('/api/v1/admin/users')->assertStatus(403);
        $this->actingAs($krama)->postJson('/api/v1/admin/users', [])->assertStatus(403);
    }
}

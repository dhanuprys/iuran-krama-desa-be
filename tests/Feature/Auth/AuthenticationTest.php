<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_register()
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'krama',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'token',
                    'user',
                ],
            ]);

        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    public function test_users_can_login()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'token',
                    'token_type',
                ],
            ]);
    }

    public function test_users_cannot_login_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'ERR-AUTH-001',
                    'message' => 'Invalid login details',
                ]
            ]);
    }

    public function test_authenticated_users_can_access_profile()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/v1/profile');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'email' => $user->email,
                ]
            ]);
    }

    public function test_authenticated_users_can_update_profile()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->putJson('/api/v1/profile', [
            'name' => 'Updated Name',
            'username' => 'updated_user',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.user.name', 'Updated Name')
            ->assertJsonPath('data.user.username', 'updated_user');

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Updated Name']);
    }

    public function test_unauthenticated_users_cannot_access_profile()
    {
        $response = $this->getJson('/api/v1/profile');

        $response->assertStatus(401);
    }

    public function test_users_can_logout()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => ['message' => 'Logged out successfully']
            ]);
    }

    public function test_users_can_check_if_they_have_resident()
    {
        $user = User::factory()->create();

        // Check without resident
        $response = $this->actingAs($user)->getJson('/api/v1/user/has-resident');
        $response->assertStatus(200)
            ->assertJsonPath('data.has_resident', false);

        // Create resident
        \App\Models\Resident::factory()->create(['user_id' => $user->id]);

        // Check with resident
        $response = $this->actingAs($user)->getJson('/api/v1/user/has-resident');
        $response->assertStatus(200)
            ->assertJsonPath('data.has_resident', true);
    }
}

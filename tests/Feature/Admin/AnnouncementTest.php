<?php

namespace Tests\Feature\Admin;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementTest extends TestCase
{
    use RefreshDatabase;

    private $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_create_announcement()
    {
        $data = [
            'title' => 'Test Announcement',
            'content' => 'This is a test content.',
            'is_active' => true,
        ];

        $response = $this->actingAs($this->admin)->postJson('/api/v1/admin/announcements', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['title' => 'Test Announcement']);
    }

    public function test_admin_can_update_announcement()
    {
        $announcement = Announcement::factory()->create();

        $response = $this->actingAs($this->admin)->putJson("/api/v1/admin/announcements/{$announcement->id}", [
            'title' => 'Updated Title',
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Updated Title']);
    }

    public function test_admin_can_delete_announcement()
    {
        $announcement = Announcement::factory()->create();

        $response = $this->actingAs($this->admin)->deleteJson("/api/v1/admin/announcements/{$announcement->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('announcements', ['id' => $announcement->id]);
    }
}

<?php

namespace Tests\Feature\Krama;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementTest extends TestCase
{
    use RefreshDatabase;

    public function test_krama_can_view_active_announcements()
    {
        $krama = User::factory()->create(['role' => 'krama']);
        Announcement::factory()->create(['title' => 'Active Announcement', 'is_active' => true]);
        Announcement::factory()->create(['title' => 'Inactive Announcement', 'is_active' => false]);

        $response = $this->actingAs($krama)->getJson('/api/v1/krama/announcements');

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Active Announcement'])
            ->assertJsonMissing(['title' => 'Inactive Announcement']);
    }
}

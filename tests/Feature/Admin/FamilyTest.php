<?php

namespace Tests\Feature\Admin;

use App\Models\Resident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FamilyTest extends TestCase
{
    use RefreshDatabase;

    private $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_list_families()
    {
        // Create 3 residents with the same KK
        $kk1 = '1111111111111111';
        Resident::factory()->create(['family_card_number' => $kk1, 'family_status' => 'HEAD_OF_FAMILY']);
        Resident::factory()->create(['family_card_number' => $kk1, 'family_status' => 'WIFE']);

        // Create another KK
        $kk2 = '2222222222222222';
        Resident::factory()->create(['family_card_number' => $kk2, 'family_status' => 'HEAD_OF_FAMILY']);

        $response = $this->actingAs($this->admin)->getJson('/api/v1/admin/families');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'family_card_number',
                        'head_of_family'
                    ]
                ],
                'pagination',
            ]);

        // Assert we have 2 families
        $this->assertCount(2, $response->json('data'));
    }

    public function test_admin_can_view_family_details()
    {
        $kk = '3333333333333333';
        Resident::factory()->create(['family_card_number' => $kk, 'family_status' => 'HEAD_OF_FAMILY']);
        Resident::factory()->create(['family_card_number' => $kk, 'family_status' => 'WIFE']);
        Resident::factory()->create(['family_card_number' => $kk, 'family_status' => 'CHILD']);

        $response = $this->actingAs($this->admin)->getJson("/api/v1/admin/families/{$kk}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'family_card_number',
                    'members'
                ]
            ]);

        $this->assertCount(3, $response->json('data.members'));
    }

    public function test_krama_cannot_access_families()
    {
        $krama = User::factory()->create(['role' => 'krama']);

        $this->actingAs($krama)->getJson('/api/v1/admin/families')->assertStatus(403);
    }
}

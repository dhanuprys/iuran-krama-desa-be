<?php

namespace Tests\Feature\Admin;

use App\Models\Banjar;
use App\Models\Resident;
use App\Models\ResidentStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ResidentTest extends TestCase
{
    use RefreshDatabase;

    private $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        Storage::fake('public');
    }

    public function test_admin_can_list_residents()
    {
        Resident::factory()->count(5)->create();

        $response = $this->actingAs($this->admin)->getJson('/api/v1/admin/residents');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'pagination',
                'meta',
            ]);
    }

    public function test_admin_can_search_residents()
    {
        Resident::factory()->create(['name' => 'Wayan Find Me', 'nik' => '9999999999999999']);
        Resident::factory()->create(['name' => 'Made Ignore Me', 'nik' => '1111111111111111']);

        // Search by Name
        $response = $this->actingAs($this->admin)->getJson('/api/v1/admin/residents?search=Find');
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['name' => 'Wayan Find Me']);

        // Search by NIK
        $responseNik = $this->actingAs($this->admin)->getJson('/api/v1/admin/residents?search=999999');
        $responseNik->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['nik' => '9999999999999999']);
    }

    public function test_admin_can_create_resident()
    {
        $user = User::factory()->create();
        $banjar = Banjar::factory()->create();
        $photo = UploadedFile::fake()->image('ktp.jpg');

        $data = [
            'nik' => '1234567890123456',
            'user_id' => $user->id,
            'banjar_id' => $banjar->id,
            'family_card_number' => '1234567890123456',
            'name' => 'Wayan Test',
            'gender' => 'L',
            'place_of_birth' => 'Denpasar',
            'date_of_birth' => '1990-01-01',
            'family_status' => 'HEAD_OF_FAMILY',
            'photo_ktp' => $photo,
        ];

        $response = $this->actingAs($this->admin)->postJson('/api/v1/admin/residents', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'Wayan Test']);

        $resident = Resident::where('nik', '1234567890123456')->first();
        $this->assertNotNull($resident->photo_ktp);
        Storage::disk('public')->assertExists($resident->photo_ktp);
    }

    public function test_admin_create_resident_validation_error()
    {
        $response = $this->actingAs($this->admin)->postJson('/api/v1/admin/residents', []);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'error' => [
                    'code',
                    'message',
                    'details' => [
                        'nik',
                        'name',
                        'gender',
                        // ... other required fields
                    ]
                ]
            ]);
    }

    public function test_admin_can_update_resident()
    {
        $resident = Resident::factory()->create();

        $response = $this->actingAs($this->admin)->putJson("/api/v1/admin/residents/{$resident->id}", [
            'name' => 'Updated Name',
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Updated Name']);

        $this->assertDatabaseHas('residents', ['id' => $resident->id, 'name' => 'Updated Name']);
    }

    public function test_admin_can_delete_resident()
    {
        $resident = Resident::factory()->create();

        $response = $this->actingAs($this->admin)->deleteJson("/api/v1/admin/residents/{$resident->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('residents', ['id' => $resident->id]);
    }

    public function test_non_admin_cannot_access_resident_management()
    {
        $krama = User::factory()->create(['role' => 'krama']);

        $response = $this->actingAs($krama)->getJson('/api/v1/admin/residents');

        $response->assertStatus(403); // Or 401/404 depending on middleware impl
    }
}

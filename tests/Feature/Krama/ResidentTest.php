<?php

namespace Tests\Feature\Krama;

use App\Models\Banjar;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ResidentTest extends TestCase
{
    use RefreshDatabase;

    private $krama;

    protected function setUp(): void
    {
        parent::setUp();
        $this->krama = User::factory()->create([
            'role' => 'krama',
            'can_create_resident' => true // Enable by default for existing tests involving creation
        ]);
        Storage::fake('public');
    }

    public function test_krama_cannot_apply_without_permission()
    {
        $restrictedKrama = User::factory()->create([
            'role' => 'krama',
            'can_create_resident' => false
        ]);

        $response = $this->actingAs($restrictedKrama)->postJson('/api/v1/krama/residents', []);

        $response->assertStatus(403)
            ->assertJsonFragment(['message' => 'Anda tidak memiliki izin untuk membuat data penduduk.']);
    }

    public function test_krama_can_apply_for_resident()
    {
        $banjar = Banjar::factory()->create();
        $photo = UploadedFile::fake()->image('house.jpg');

        $data = [
            'nik' => '1234567890123456',
            'family_card_number' => '1234567890123456',
            'name' => 'Krama Applicant',
            'gender' => 'L',
            'place_of_birth' => 'Denpasar',
            'date_of_birth' => '2000-01-01',
            'family_status' => 'HEAD_OF_FAMILY',
            'banjar_id' => $banjar->id,
            'photo_house' => $photo,
        ];

        $response = $this->actingAs($this->krama)->postJson('/api/v1/krama/residents', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['validation_status' => 'PENDING', 'created_by_user_id' => $this->krama->id]);

        $this->assertDatabaseHas('residents', ['name' => 'Krama Applicant']);
        // Check if file is stored (name is hashed so we just check existence of any file in directory or check DB column has path)
        $resident = Resident::where('nik', '1234567890123456')->first();
        $this->assertNotNull($resident->photo_house);
        Storage::disk('public')->assertExists($resident->photo_house);
    }

    public function test_krama_apply_resident_validation_fail()
    {
        $response = $this->actingAs($this->krama)->postJson('/api/v1/krama/residents', []);

        $response->assertStatus(422)
            ->assertJsonStructure(['error' => ['details' => ['nik', 'name']]]);
    }

    public function test_krama_can_view_own_residents()
    {
        Resident::factory()->create([
            'user_id' => $this->krama->id,
            'name' => 'My Resident',
        ]);

        $response = $this->actingAs($this->krama)->getJson('/api/v1/krama/residents');

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'My Resident']);
    }

    public function test_krama_cannot_view_others_residents()
    {
        $otherUser = User::factory()->create();
        Resident::factory()->create([
            'user_id' => $otherUser->id,
            'name' => 'Other Resident',
        ]);

        $response = $this->actingAs($this->krama)->getJson('/api/v1/krama/residents');

        $response->assertStatus(200)
            ->assertJsonMissing(['name' => 'Other Resident']);
    }

    public function test_krama_can_update_pending_resident()
    {
        $banjar = Banjar::factory()->create();
        $resident = Resident::factory()->create([
            'user_id' => $this->krama->id,
            'validation_status' => 'PENDING',
        ]);

        $data = [
            'nik' => $resident->nik,
            'family_card_number' => '8888888888888888',
            'name' => 'Updated Name',
            'gender' => 'P',
            'place_of_birth' => 'Ubud',
            'date_of_birth' => '1995-05-05',
            'family_status' => 'WIFE',
            'banjar_id' => $banjar->id,
        ];

        $response = $this->actingAs($this->krama)->putJson("/api/v1/krama/residents/{$resident->id}", $data);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Updated Name']);

        $this->assertDatabaseHas('residents', ['id' => $resident->id, 'name' => 'Updated Name']);
    }

    public function test_krama_cannot_update_approved_resident()
    {
        $resident = Resident::factory()->create([
            'user_id' => $this->krama->id,
            'validation_status' => 'APPROVED',
        ]);

        $response = $this->actingAs($this->krama)->putJson("/api/v1/krama/residents/{$resident->id}", [
            'name' => 'Should Not Update',
        ]);

        $response->assertStatus(422) // VALIDATION_ERROR
            ->assertJsonFragment(['message' => 'Only pending applications can be updated']);
    }
}

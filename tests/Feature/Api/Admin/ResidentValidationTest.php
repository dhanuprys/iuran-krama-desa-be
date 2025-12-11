<?php

namespace Tests\Feature\Api\Admin;

use App\Models\User;
use App\Models\Resident;
use App\Models\Banjar;
use App\Models\ResidentStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ResidentValidationTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $banjar;
    protected $residentStatus;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup common data
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->banjar = Banjar::factory()->create();
        $this->residentStatus = ResidentStatus::factory()->create();
    }

    #[Test]
    public function head_of_family_requires_all_fields()
    {
        Storage::fake('public');

        // Minimal data that would pass for a regular member but fail for Head
        $data = [
            'nik' => '1234567890123456',
            'user_id' => $this->admin->id, // required by store logic
            'family_card_number' => '1234567890123456',
            'name' => 'Pak Kepala',
            'gender' => 'L',
            'place_of_birth' => 'Denpasar',
            'date_of_birth' => '1980-01-01',
            'family_status' => 'HEAD_OF_FAMILY',
            // Missing: banjar_id, religion, education, work_type, marital_status,
            // origin_address, residential_address, resident_status_id, etc.
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/admin/residents', $data);

        // Expect validation error
        if ($response->status() !== 422) {
            dump($response->json());
        }
        $response->assertStatus(422)
            ->assertJsonStructure([
                'error' => [
                    'details' => [
                        'banjar_id',
                        'religion',
                        'education',
                        'work_type',
                        'marital_status',
                        'origin_address',
                        'residential_address',
                        'phone',
                        'email',
                        'photo_house',
                        'resident_photo',
                        'photo_ktp',
                        'resident_status_id'
                    ]
                ]
            ]);
    }

    #[Test]
    public function head_of_family_succeeds_with_all_fields()
    {
        Storage::fake('public');

        $data = [
            'nik' => '9999999999999999',
            'user_id' => $this->admin->id,
            'family_card_number' => '1234567890123456',
            'name' => 'Pak Kepala Lengkap',
            'gender' => 'L',
            'place_of_birth' => 'Denpasar',
            'date_of_birth' => '1980-01-01',
            'family_status' => 'HEAD_OF_FAMILY',
            'banjar_id' => $this->banjar->id,
            'resident_status_id' => $this->residentStatus->id,
            'religion' => 'Hindu',
            'education' => 'S1',
            'work_type' => 'Swasta',
            'marital_status' => 'MARRIED',
            'origin_address' => 'Jl. Asal No 1',
            'residential_address' => 'Jl. Desa No 1',
            'rt_number' => '001',
            'residence_name' => 'Griya',
            'house_number' => '10',
            'phone' => '08123456789',
            'email' => 'head@example.com',
            'photo_house' => UploadedFile::fake()->image('house.jpg'),
            'resident_photo' => UploadedFile::fake()->image('photo.jpg'),
            'photo_ktp' => UploadedFile::fake()->image('ktp.jpg'),
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/admin/residents', $data);

        $response->assertStatus(201);
    }

    #[Test]
    public function regular_family_member_succeeds_with_minimal_fields()
    {
        // Child or Wife doesn't need address/photos/status as they inherit/are irrelevant
        $data = [
            'nik' => '8888888888888888',
            'user_id' => $this->admin->id,
            'family_card_number' => '1234567890123456',
            'name' => 'Anak Kecil',
            'gender' => 'L',
            'place_of_birth' => 'Denpasar',
            'date_of_birth' => '2010-01-01',
            'family_status' => 'CHILD',
            // All other fields omitted
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/admin/residents', $data);

        $response->assertStatus(201);
    }

    #[Test]
    public function operator_role_also_enforces_validation()
    {
        // Create operator user
        $operator = User::factory()->create(['role' => 'operator']);

        $data = [
            'nik' => '7777777777777777',
            'user_id' => $operator->id, // If operator passes their own ID
            'family_card_number' => '1234567890123456',
            'name' => 'Pak Operator Input',
            'gender' => 'L',
            'place_of_birth' => 'Denpasar',
            'date_of_birth' => '1980-01-01',
            'family_status' => 'HEAD_OF_FAMILY',
            // Missing required fields
        ];

        $response = $this->actingAs($operator)
            ->postJson('/api/v1/operator/residents', $data);

        if ($response->status() !== 422) {
            dump($response->json());
        }

        $response->assertStatus(422);
        // Check for error details in custom structure
        $response->assertJsonStructure([
            'error' => [
                'details' => [
                    'banjar_id'
                ]
            ]
        ]);
    }
}

<?php

namespace Tests\Feature\Admin;

use App\Models\Resident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ResidentValidationTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $krama;

    protected function setUp(): void
    {
        parent::setUp();
        // Create Admin
        $this->admin = User::factory()->create(['role' => 'admin']);
        // Create Krama
        $this->krama = User::factory()->create(['role' => 'krama']);
    }

    public function test_admin_can_approve_pending_resident()
    {
        $resident = Resident::factory()->create([
            'user_id' => $this->krama->id,
            'validation_status' => 'PENDING'
        ]);

        $response = $this->actingAs($this->admin)->postJson("/api/v1/admin/residents/{$resident->id}/validate", [
            'status' => 'APPROVED'
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.validation_status', 'APPROVED');

        $this->assertDatabaseHas('residents', [
            'id' => $resident->id,
            'validation_status' => 'APPROVED'
        ]);
    }

    public function test_admin_can_reject_pending_resident_with_reason()
    {
        $resident = Resident::factory()->create([
            'user_id' => $this->krama->id,
            'validation_status' => 'PENDING'
        ]);

        $response = $this->actingAs($this->admin)->postJson("/api/v1/admin/residents/{$resident->id}/validate", [
            'status' => 'REJECTED',
            'rejection_reason' => 'Data tidak lengkap'
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.validation_status', 'REJECTED');

        $this->assertDatabaseHas('residents', [
            'id' => $resident->id,
            'validation_status' => 'REJECTED',
            'rejection_reason' => 'Data tidak lengkap'
        ]);
    }

    public function test_cannot_reject_without_reason()
    {
        $resident = Resident::factory()->create([
            'user_id' => $this->krama->id,
            'validation_status' => 'PENDING'
        ]);

        $response = $this->actingAs($this->admin)->postJson("/api/v1/admin/residents/{$resident->id}/validate", [
            'status' => 'REJECTED'
        ]);

        $response->assertStatus(422);
    }

    public function test_cannot_validate_already_approved_resident()
    {
        $resident = Resident::factory()->create([
            'user_id' => $this->krama->id,
            'validation_status' => 'APPROVED'
        ]);

        $response = $this->actingAs($this->admin)->postJson("/api/v1/admin/residents/{$resident->id}/validate", [
            'status' => 'REJECTED',
            'rejection_reason' => 'Mistake'
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 'ERR-BIZ-005');
    }

    public function test_krama_cannot_validate_resident()
    {
        $resident = Resident::factory()->create([
            'user_id' => $this->krama->id,
            'validation_status' => 'PENDING'
        ]);

        $response = $this->actingAs($this->krama)->postJson("/api/v1/admin/residents/{$resident->id}/validate", [
            'status' => 'APPROVED'
        ]);

        $response->assertStatus(403); // Middleware check
    }
}

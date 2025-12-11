<?php

namespace Tests\Feature\Admin;

use App\Models\Invoice;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    private $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_create_invoice()
    {
        $resident = Resident::factory()->create(['family_status' => 'HEAD_OF_FAMILY']);
        // Ensure resident status has known contribution amount
        $resident->residentStatus->update(['contribution_amount' => 50000]);

        $data = [
            'resident_id' => $resident->id,
            'invoice_date' => '2025-12-01',
            // iuran_amount is ignored by controller, taken from resident status
            'peturunan_amount' => 10000,
            'dedosan_amount' => 0,
        ];

        $response = $this->actingAs($this->admin)->postJson('/api/v1/admin/invoices', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['total_amount' => '60000.00']);
    }

    public function test_admin_cannot_create_duplicate_invoice_in_same_month()
    {
        $resident = Resident::factory()->create(['family_status' => 'HEAD_OF_FAMILY']);

        Invoice::factory()->create([
            'resident_id' => $resident->id,
            'invoice_date' => '2025-12-01',
        ]);

        $data = [
            'resident_id' => $resident->id,
            'invoice_date' => '2025-12-05', // Same month
            'iuran_amount' => 50000,
            'peturunan_amount' => 10000,
            'dedosan_amount' => 0,
        ];

        $response = $this->actingAs($this->admin)->postJson('/api/v1/admin/invoices', $data);

        $response->assertStatus(409) // APP_ERROR: INVOICE_DUPLICATE
            ->assertJsonFragment(['code' => 'ERR-BIZ-002']);
    }

    public function test_admin_create_invoice_validation_error()
    {
        $response = $this->actingAs($this->admin)->postJson('/api/v1/admin/invoices', []);

        $response->assertStatus(422)
            ->assertJsonStructure(['error' => ['details' => ['resident_id', 'invoice_date']]]);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoicePrintTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_download_invoice()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $resident = Resident::factory()->create();
        $invoice = Invoice::create([
            'resident_id' => $resident->id,
            'invoice_date' => now(),
            'total_amount' => 100000,
            'iuran_amount' => 100000,
            'peturunan_amount' => 0,
            'dedosan_amount' => 0,
            'user_id' => $admin->id
        ]);

        $response = $this->actingAs($admin)->get("/api/v1/admin/invoices/{$invoice->id}/download");

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_operator_can_download_invoice()
    {
        $operator = User::factory()->create(['role' => 'operator']);
        $resident = Resident::factory()->create();
        $invoice = Invoice::create([
            'resident_id' => $resident->id,
            'invoice_date' => now(),
            'total_amount' => 100000,
            'iuran_amount' => 100000,
            'peturunan_amount' => 0,
            'dedosan_amount' => 0,
            'user_id' => $operator->id
        ]);

        $response = $this->actingAs($operator)->get("/api/v1/operator/invoices/{$invoice->id}/download");

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_krama_can_download_own_invoice()
    {
        $krama = User::factory()->create(['role' => 'krama']);
        $resident = Resident::factory()->create(['user_id' => $krama->id]);
        $invoice = Invoice::create([
            'resident_id' => $resident->id,
            'invoice_date' => now(),
            'total_amount' => 100000,
            'iuran_amount' => 100000,
            'peturunan_amount' => 0,
            'dedosan_amount' => 0,
            'user_id' => $krama->id // doesn't matter who created it
        ]);

        $response = $this->actingAs($krama)->get("/api/v1/krama/invoices/{$invoice->id}/download");

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_krama_cannot_download_others_invoice()
    {
        $krama = User::factory()->create(['role' => 'krama']);
        $otherKrama = User::factory()->create(['role' => 'krama']);
        $resident = Resident::factory()->create(['user_id' => $otherKrama->id]); // Belong to other
        $invoice = Invoice::create([
            'resident_id' => $resident->id,
            'invoice_date' => now(),
            'total_amount' => 100000,
            'iuran_amount' => 100000,
            'peturunan_amount' => 0,
            'dedosan_amount' => 0,
            'user_id' => $otherKrama->id
        ]);

        $response = $this->actingAs($krama)->get("/api/v1/krama/invoices/{$invoice->id}/download");

        $response->assertStatus(403);
    }
}

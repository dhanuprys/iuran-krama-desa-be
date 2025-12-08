<?php

namespace Tests\Feature\Krama;

use App\Models\Invoice;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    private $krama;

    protected function setUp(): void
    {
        parent::setUp();
        $this->krama = User::factory()->create(['role' => 'krama']);
    }

    public function test_krama_can_view_own_invoices()
    {
        $resident = Resident::factory()->create(['user_id' => $this->krama->id]);
        $invoice = Invoice::factory()->create(['resident_id' => $resident->id, 'total_amount' => 12345]);

        $response = $this->actingAs($this->krama)->getJson("/api/v1/krama/invoices?resident_id={$resident->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['total_amount' => '12345.00']);
    }

    public function test_krama_cannot_view_others_invoices()
    {
        $otherUser = User::factory()->create();
        $resident = Resident::factory()->create(['user_id' => $otherUser->id]);
        $invoice = Invoice::factory()->create(['resident_id' => $resident->id, 'total_amount' => 99999]);

        // Trying to access other's resident_id directly
        $response = $this->actingAs($this->krama)->getJson("/api/v1/krama/invoices?resident_id={$resident->id}");

        $response->assertStatus(403);
    }

    public function test_krama_cannot_view_invoices_without_resident_context()
    {
        $response = $this->actingAs($this->krama)->getJson('/api/v1/krama/invoices');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['resident_id']);
    }

    public function test_krama_cannot_access_specific_invoice_of_others()
    {
        $otherUser = User::factory()->create();
        $resident = Resident::factory()->create(['user_id' => $otherUser->id]);
        $invoice = Invoice::factory()->create(['resident_id' => $resident->id]);

        $response = $this->actingAs($this->krama)->getJson("/api/v1/krama/invoices/{$invoice->id}");

        $response->assertStatus(403); // FORBIDDEN
    }
}

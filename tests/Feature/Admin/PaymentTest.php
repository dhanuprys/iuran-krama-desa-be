<?php

namespace Tests\Feature\Admin;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Invoice $invoice;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $resident = Resident::factory()->create();
        $this->invoice = Invoice::factory()->create(['resident_id' => $resident->id]);
    }

    public function test_admin_can_list_payments()
    {
        Payment::factory()->count(3)->create(['invoice_id' => $this->invoice->id]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/admin/payments');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_admin_can_create_payment()
    {
        $payload = [
            'invoice_id' => $this->invoice->id,
            'amount' => 50000,
            'date' => now()->format('Y-m-d'),
            'method' => 'cash',
            'status' => 'paid',
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/admin/payments', $payload);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'amount' => '50000.00',
                'method' => 'cash',
                'status' => 'paid',
            ]);

        $this->assertDatabaseHas('payments', [
            'invoice_id' => $this->invoice->id,
            'amount' => 50000,
        ]);
    }

    public function test_admin_can_view_payment()
    {
        $payment = Payment::factory()->create(['invoice_id' => $this->invoice->id]);

        $response = $this->actingAs($this->admin)
            ->getJson("/api/v1/admin/payments/{$payment->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $payment->id);
    }

    public function test_admin_can_update_payment()
    {
        $payment = Payment::factory()->create(['invoice_id' => $this->invoice->id, 'status' => 'pending']);

        $payload = [
            'status' => 'paid',
            'method' => 'transfer',
        ];

        $response = $this->actingAs($this->admin)
            ->putJson("/api/v1/admin/payments/{$payment->id}", $payload);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'status' => 'paid',
                'method' => 'transfer',
            ]);

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'paid',
        ]);
    }

    public function test_admin_can_delete_payment()
    {
        $payment = Payment::factory()->create(['invoice_id' => $this->invoice->id]);

        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/v1/admin/payments/{$payment->id}");

        $response->assertStatus(200);

        $this->assertSoftDeleted('payments', ['id' => $payment->id]);
    }

    public function test_cannot_create_payment_for_invalid_invoice()
    {
        $payload = [
            'invoice_id' => 99999,
            'amount' => 50000,
            'date' => now()->format('Y-m-d'),
            'method' => 'cash',
            'status' => 'paid',
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/admin/payments', $payload);

        $response->assertStatus(422) // Validation error
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.code', 'ERR-VAL-001')
            ->assertJsonStructure(['error' => ['details' => ['invoice_id']]]);
    }
}

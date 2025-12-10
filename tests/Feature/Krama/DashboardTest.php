<?php

namespace Tests\Feature\Krama;

use App\Models\Invoice;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_access_dashboard_without_resident_id()
    {
        $user = User::factory()->create(['role' => 'KRAMA']);
        $this->actingAs($user);

        $response = $this->getJson('/api/v1/krama/dashboard');

        $response->assertStatus(422)
            ->assertJsonStructure(['success', 'error' => ['code', 'message', 'details' => ['resident_id']]]);
    }

    public function test_cannot_access_dashboard_of_other_user_resident()
    {
        $user = User::factory()->create(['role' => 'KRAMA']);
        $otherUser = User::factory()->create(['role' => 'KRAMA']);
        $otherResident = Resident::factory()->create(['user_id' => $otherUser->id]);

        $this->actingAs($user);

        $response = $this->getJson("/api/v1/krama/dashboard?resident_id={$otherResident->id}");

        $response->assertStatus(403)
            ->assertJsonPath('error.code', 'ERR-AUTH-003');
    }

    public function test_can_get_dashboard_stats()
    {
        $user = User::factory()->create(['role' => 'KRAMA']);
        $resident = Resident::factory()->create(['user_id' => $user->id]);

        // Create invoices
        // Create unpaid invoice (Total: 50,000)
        Invoice::factory()->create([
            'resident_id' => $resident->id,
            'total_amount' => 50000
        ]);

        // Create paid invoice (Total: 75,000)
        $paidInvoice = Invoice::factory()->create([
            'resident_id' => $resident->id,
            'total_amount' => 75000
        ]);

        \App\Models\Payment::factory()->create([
            'invoice_id' => $paidInvoice->id,
            'amount' => 75000,
            'status' => 'paid'
        ]);

        $this->actingAs($user);

        $response = $this->getJson("/api/v1/krama/dashboard?resident_id={$resident->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_unpaid_amount',
                    'total_paid_amount',
                    'recent_invoices'
                ]
            ])
            ->assertJsonPath('data.total_unpaid_amount', 50000)
            ->assertJsonPath('data.total_paid_amount', 75000);
    }
}

<?php

namespace Tests\Feature\Operator;

use App\Models\Invoice;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_operator_can_list_invoices()
    {
        $operator = User::factory()->create(['role' => 'operator']);

        Invoice::factory()->count(3)->create();

        $response = $this->actingAs($operator)->getJson('/api/v1/operator/invoices');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'total_amount', 'resident']
                ]
            ]);
    }

    public function test_operator_can_create_invoice()
    {
        $operator = User::factory()->create(['role' => 'operator']);
        $resident = Resident::factory()->create(['family_status' => 'HEAD_OF_FAMILY']);

        $data = [
            'resident_id' => $resident->id,
            'invoice_date' => now()->format('Y-m-d'),
            'peturunan_amount' => 10000,
            'dedosan_amount' => 5000,
        ];

        $response = $this->actingAs($operator)->postJson('/api/v1/operator/invoices', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.resident.id', $resident->id);

        $this->assertDatabaseHas('invoices', [
            'resident_id' => $resident->id,
            'peturunan_amount' => 10000
        ]);
    }
}

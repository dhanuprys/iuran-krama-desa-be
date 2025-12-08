<?php

namespace Tests\Feature\Admin;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_audit_logs()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        AuditLog::create([
            'user_id' => $admin->id,
            'action' => 'TEST_ACTION',
            'model_type' => 'App\Models\User',
            'model_id' => 1,
        ]);

        $response = $this->actingAs($admin)->getJson('/api/v1/admin/audit-logs');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'pagination']);
    }
}

<?php

namespace Tests\Feature\Admin;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $logs;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->logs = AuditLog::factory()->count(15)->create();
    }

    public function test_admin_can_view_audit_logs_list()
    {
        $response = $this->actingAs($this->admin)->getJson('/api/v1/admin/audit-logs');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'user_id',
                        'action',
                        'model_type',
                        'model_id',
                        'created_at',
                        'user' => ['id', 'name']
                    ]
                ],
                'pagination',
                'meta'
            ]);
    }

    public function test_admin_can_filter_audit_logs()
    {
        $targetLog = $this->logs->first();

        $response = $this->actingAs($this->admin)->getJson('/api/v1/admin/audit-logs?action=' . $targetLog->action);

        $response->assertStatus(200);
        // We might get multiple logs with same action, but should get at least one
        $this->assertGreaterThanOrEqual(1, count($response->json('data')));
    }

    public function test_admin_can_view_audit_log_detail()
    {
        $log = $this->logs->first();

        $response = $this->actingAs($this->admin)->getJson('/api/v1/admin/audit-logs/' . $log->id);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $log->id,
                    'action' => $log->action,
                    'model_type' => $log->model_type,
                    // 'old_values' and 'new_values' might be null or array, check structure if needed
                ]
            ]);
    }

    public function test_non_admin_cannot_access_audit_logs()
    {
        $user = User::factory()->create(['role' => 'krama']);

        $response = $this->actingAs($user)->getJson('/api/v1/admin/audit-logs');

        $response->assertStatus(403);
    }
}

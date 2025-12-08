<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = AuditLog::with('user');

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('model_type')) {
            $query->where('model_type', $request->model_type);
        }

        if ($request->has('model_id')) {
            $query->where('model_id', $request->model_id);
        }

        if ($request->has('action')) {
            $query->where('action', $request->action);
        }

        $logs = $query->latest()->paginate(20);

        return $this->paginated($logs);
    }

    public function show(AuditLog $auditLog): JsonResponse
    {
        return $this->success($auditLog->load('user'));
    }
}

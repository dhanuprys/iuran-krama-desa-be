<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ResidentStatus;
use Illuminate\Http\Request;

class ResidentStatusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $statuses = ResidentStatus::select('id', 'name', 'contribution_amount')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Resident statuses retrieved successfully.',
            'data' => $statuses
        ]);
    }
}

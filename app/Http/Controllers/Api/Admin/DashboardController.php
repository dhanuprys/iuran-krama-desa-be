<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Family;
use App\Models\Invoice;
use App\Models\Resident;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use ApiResponse;

    /**
     * Get dashboard statistics.
     */
    public function index(): JsonResponse
    {
        $stats = [
            'total_residents' => Resident::count(),
            'total_families' => Family::count(), // Using the View Count
            'pending_residents' => Resident::where('validation_status', 'PENDING')->count(),
            'total_users' => User::count(),
            'total_invoices' => Invoice::count(),
            'invoice_summary' => [
                'total_amount' => Invoice::sum('total_amount'),
                'total_paid' => Invoice::where('status', 'PAID')->sum('total_amount'),
                'total_unpaid' => Invoice::where('status', 'UNPAID')->sum('total_amount'),
            ]
        ];

        return $this->success($stats);
    }
}

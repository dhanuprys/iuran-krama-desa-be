<?php

namespace App\Http\Controllers\Api\Krama;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Resident;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DashboardController extends Controller
{
    use \App\Traits\ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'resident_id' => 'required|exists:residents,id',
        ]);

        if ($validator->fails()) {
            return $this->error('VALIDATION_ERROR', $validator->errors());
        }

        $residentId = $request->resident_id;
        $user = $request->user();

        // Verify the resident belongs to the authenticated user
        $resident = Resident::where('id', $residentId)->where('user_id', $user->id)->first();

        if (!$resident) {
            return $this->error('FORBIDDEN_ACCESS', null, 'You do not have access to this resident data.', 403);
        }

        // Fetch all invoices with payments
        $invoices = Invoice::where('resident_id', $residentId)
            ->with(['payments'])
            ->get();

        $totalUnpaid = 0;
        $totalPaid = 0;

        foreach ($invoices as $invoice) {
            $paidAmount = $invoice->payments->sum('amount');
            $remaining = max(0, $invoice->total_amount - $paidAmount);

            $totalPaid += $paidAmount;
            $totalUnpaid += $remaining;
        }

        $unpaidInvoices = $totalUnpaid;
        $paidInvoices = $totalPaid;

        // Recent Activity (Latest 5 invoices)
        $recentInvoices = Invoice::where('resident_id', $residentId)
            ->with(['payments'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return $this->success([
            'total_unpaid_amount' => $unpaidInvoices,
            'total_paid_amount' => $paidInvoices,
            'recent_invoices' => $recentInvoices
        ]);
    }
}

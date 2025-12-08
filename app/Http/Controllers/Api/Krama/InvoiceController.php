<?php

namespace App\Http\Controllers\Api\Krama;

use App\Http\Controllers\Controller;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'resident_id' => 'required|integer|exists:residents,id',
        ]);

        $user = $request->user();
        $residentId = $request->input('resident_id');

        // Check ownership
        $resident = $user->residents()->where('id', $residentId)->first();

        if (!$resident) {
            return $this->error('FORBIDDEN', null, 'You do not have access to this resident data', 403);
        }

        $query = Invoice::with(['resident', 'resident.residentStatus', 'resident.banjar'])
            ->where('resident_id', $resident->id);

        if ($request->has('sort_by') && in_array($request->sort_by, ['total_amount', 'invoice_date', 'created_at', 'updated_at'])) {
            $sortOrder = $request->input('sort_order', 'desc');
            $query->orderBy($request->sort_by, $sortOrder);
        } else {
            $query->orderBy('invoice_date', 'desc');
        }

        $invoices = $query->paginate($request->input('per_page', 15));

        return $this->paginated(InvoiceResource::collection($invoices));
    }

    public function show(string $id): JsonResponse
    {
        $invoice = Invoice::with(['resident', 'payments'])->find($id);

        if (!$invoice) {
            return $this->error('RESOURCE_NOT_FOUND');
        }

        // Authorization: Check if the invoice belongs to a resident owned by the user
        if ($invoice->resident->user_id !== auth()->id()) {
            return $this->error('FORBIDDEN');
        }

        return $this->success(new InvoiceResource($invoice));
    }
}

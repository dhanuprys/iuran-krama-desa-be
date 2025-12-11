<?php

namespace App\Http\Controllers\Api\Operator;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    use \App\Traits\ApiResponse, \App\Traits\GeneratesReceiptPdf;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Payment::query();

        $query->select([
            'payments.id',
            'payments.invoice_id',
            'payments.amount',
            'payments.date',
            'payments.method',
            'payments.status',
            'payments.user_id',
            'payments.created_at',
            'payments.updated_at'
        ]);

        $query->with([
            'invoice.resident:id,name,resident_status_id',
            'invoice.resident.residentStatus:id,name',
            'user:id,name'
        ]);

        // Filter by invoice if provided
        if ($request->filled('invoice_id')) {
            $query->where('payments.invoice_id', $request->invoice_id);
        }

        // Filter by resident (via invoice)
        if ($request->filled('resident_id')) {
            $query->whereHas('invoice', function ($q) use ($request) {
                $q->where('resident_id', $request->resident_id);
            });
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->where('payments.date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('payments.date', '<=', $request->end_date);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('payments.status', $request->status);
        }

        // Sort options
        $sortBy = $request->get('sort_by', 'date');
        $sortOrder = $request->get('sort_order', 'desc');

        $allowedSortFields = ['date', 'amount', 'created_at'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy('payments.' . $sortBy, $sortOrder);
        }

        $perPage = min($request->get('per_page', 15), 100);
        $payments = $query->paginate($perPage);

        return $this->paginated(PaymentResource::collection($payments));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'invoice_id' => 'required|integer|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date|before_or_equal:today',
            'method' => 'required|string|max:50', // e.g., 'cash', 'transfer'
            'status' => 'required|in:paid,pending,invalid',
        ]);

        if ($validator->fails()) {
            return $this->error('VALIDATION_ERROR', $validator->errors());
        }

        $data = $validator->validated();

        $data['user_id'] = auth()->id();

        $payment = Payment::create($data);

        return $this->success(new PaymentResource($payment->load(['invoice', 'user'])), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $payment = Payment::with(['invoice.resident', 'user'])->find($id);

        if (!$payment) {
            return $this->error('RESOURCE_NOT_FOUND');
        }

        return $this->success(new PaymentResource($payment));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        // ... (existing update logic)
        $payment = Payment::find($id);

        if (!$payment) {
            return $this->error('RESOURCE_NOT_FOUND');
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'sometimes|numeric|min:0.01',
            'date' => 'sometimes|date|before_or_equal:today',
            'method' => 'sometimes|string|max:50',
            'status' => 'sometimes|in:paid,pending,invalid',
        ]);

        if ($validator->fails()) {
            return $this->error('VALIDATION_ERROR', $validator->errors());
        }

        $payment->update($validator->validated());

        return $this->success(new PaymentResource($payment->load(['invoice', 'user'])));
    }

    /**
     * Download the receipt PDF.
     */
    public function download(Payment $payment)
    {
        return $this->downloadReceipt($payment);
    }
}

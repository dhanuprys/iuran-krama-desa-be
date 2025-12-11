<?php

namespace App\Http\Controllers\Api\Operator;

use App\Http\Controllers\Controller;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Models\Resident;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InvoiceController extends Controller
{
    use \App\Traits\ApiResponse;

    /**
     * Check if resident already has an invoice for the given month and year
     */
    private function hasDuplicateMonthlyInvoice(int $residentId, Carbon $invoiceDate, ?int $excludeId = null): bool
    {
        $query = Invoice::where('resident_id', $residentId)
            ->whereYear('invoice_date', $invoiceDate->year)
            ->whereMonth('invoice_date', $invoiceDate->month);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Invoice::query();

        $query->select([
            'invoices.id',
            'invoices.invoice_date',
            'invoices.total_amount',
            'invoices.resident_id',
            'invoices.iuran_amount',
            'invoices.peturunan_amount',
            'invoices.dedosan_amount',
            'invoices.created_at',
            'invoices.updated_at'
        ]);

        $query->with([
            'resident:id,name,nik',
            'payments:id,invoice_id,amount,status'
        ]);

        // Filter by resident if provided
        if ($request->filled('resident_id')) {
            $query->where('invoices.resident_id', $request->resident_id);
        }

        // Filter by date range if provided
        if ($request->filled('start_date')) {
            $query->where('invoices.invoice_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('invoices.invoice_date', '<=', $request->end_date);
        }

        // Search by resident name, NIK, or phone
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->whereHas('resident', function ($q) use ($searchTerm) {
                    $q->where('name', 'like', '%' . $searchTerm . '%')
                        ->orWhere('nik', 'like', '%' . $searchTerm . '%')
                        ->orWhere('phone', 'like', '%' . $searchTerm . '%');
                })
                    ->orWhere('invoices.id', 'like', '%' . $searchTerm . '%');
            });
        }

        // Sort options
        $sortBy = $request->get('sort_by', 'invoice_date');
        $sortOrder = $request->get('sort_order', 'desc');

        $allowedSortFields = ['invoice_date', 'total_amount', 'created_at', 'updated_at'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy('invoices.' . $sortBy, $sortOrder);
        }

        $perPage = min($request->get('per_page', 15), 100); // Max 100 per page
        $invoices = $query->paginate($perPage);

        return $this->paginated(InvoiceResource::collection($invoices));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'resident_id' => 'required|integer|exists:residents,id',
            'invoice_date' => 'required|date|before_or_equal:today',
            'peturunan_amount' => 'required|numeric|min:0|max:999999.99',
            'dedosan_amount' => 'required|numeric|min:0|max:999999.99',
        ], [
            'resident_id.required' => 'ID penduduk wajib diisi.',
            'resident_id.integer' => 'ID penduduk harus berupa angka.',
            'resident_id.exists' => 'Penduduk yang dipilih tidak ditemukan.',
            'invoice_date.required' => 'Tanggal invoice wajib diisi.',
            'invoice_date.date' => 'Format tanggal invoice tidak valid.',
            'invoice_date.before_or_equal' => 'Tanggal invoice tidak boleh di masa depan.',
            'peturunan_amount.required' => 'Jumlah peturunan wajib diisi.',
            'peturunan_amount.numeric' => 'Jumlah peturunan harus berupa angka.',
            'peturunan_amount.min' => 'Jumlah peturunan tidak boleh kurang dari 0.',
            'peturunan_amount.max' => 'Jumlah peturunan tidak boleh melebihi 999.999,99.',
            'dedosan_amount.required' => 'Jumlah dedosan wajib diisi.',
            'dedosan_amount.numeric' => 'Jumlah dedosan harus berupa angka.',
            'dedosan_amount.min' => 'Jumlah dedosan tidak boleh kurang dari 0.',
            'dedosan_amount.max' => 'Jumlah dedosan tidak boleh melebihi 999.999,99.',
        ]);

        if ($validator->fails()) {
            return $this->error('VALIDATION_ERROR', $validator->errors());
        }

        $data = $validator->validated();

        // Get resident with resident status to get contribution amount
        $resident = Resident::with('residentStatus')->find($data['resident_id']);
        if (!$resident) {
            return $this->error('RESOURCE_NOT_FOUND');
        }

        // Set iuran_amount from resident's contribution amount
        $data['iuran_amount'] = $resident->residentStatus->contribution_amount;

        // Check if resident already has an invoice for the same month and year
        $invoiceDate = Carbon::parse($data['invoice_date']);

        if ($this->hasDuplicateMonthlyInvoice($data['resident_id'], $invoiceDate)) {
            return $this->error('INVOICE_DUPLICATE', [
                'resident_id' => ['Penduduk ini sudah memiliki invoice untuk ' . $invoiceDate->format('F Y')]
            ]);
        }

        $data['total_amount'] = $data['iuran_amount'] + $data['peturunan_amount'] + $data['dedosan_amount'];
        $data['user_id'] = auth()->id();

        $invoice = Invoice::create($data);

        return $this->success(new InvoiceResource($invoice->load(['resident', 'user'])), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $invoice = Invoice::with(['resident', 'user', 'payments'])->find($id);

        if (!$invoice) {
            return $this->error('RESOURCE_NOT_FOUND');
        }

        return $this->success(new InvoiceResource($invoice));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $invoice = Invoice::find($id);

        if (!$invoice) {
            return $this->error('RESOURCE_NOT_FOUND');
        }

        $validator = Validator::make($request->all(), [
            'resident_id' => 'sometimes|integer|exists:residents,id',
            'invoice_date' => 'sometimes|date|before_or_equal:today',
            'peturunan_amount' => 'sometimes|numeric|min:0|max:999999.99',
            'dedosan_amount' => 'sometimes|numeric|min:0|max:999999.99',
        ], [
            'resident_id.integer' => 'ID penduduk harus berupa angka.',
            'resident_id.exists' => 'Penduduk yang dipilih tidak ditemukan.',
            'invoice_date.date' => 'Format tanggal invoice tidak valid.',
            'invoice_date.before_or_equal' => 'Tanggal invoice tidak boleh di masa depan.',
            'peturunan_amount.numeric' => 'Jumlah peturunan harus berupa angka.',
            'peturunan_amount.min' => 'Jumlah peturunan tidak boleh kurang dari 0.',
            'peturunan_amount.max' => 'Jumlah peturunan tidak boleh melebihi 999.999,99.',
            'dedosan_amount.numeric' => 'Jumlah dedosan harus berupa angka.',
            'dedosan_amount.min' => 'Jumlah dedosan tidak boleh kurang dari 0.',
            'dedosan_amount.max' => 'Jumlah dedosan tidak boleh melebihi 999.999,99.',
        ]);

        if ($validator->fails()) {
            return $this->error('VALIDATION_ERROR', $validator->errors());
        }

        $data = $validator->validated();

        // If resident_id is being updated, get the new resident's contribution amount
        if (isset($data['resident_id'])) {
            $resident = Resident::with('residentStatus')->find($data['resident_id']);
            if (!$resident) {
                return $this->error('RESOURCE_NOT_FOUND');
            }
            $data['iuran_amount'] = $resident->residentStatus->contribution_amount;
        }

        // Check if updating resident_id or invoice_date would create a duplicate monthly invoice
        if (isset($data['resident_id']) || isset($data['invoice_date'])) {
            $residentId = $data['resident_id'] ?? $invoice->resident_id;
            $invoiceDate = Carbon::parse($data['invoice_date'] ?? $invoice->invoice_date);

            if ($this->hasDuplicateMonthlyInvoice($residentId, $invoiceDate, $invoice->id)) {
                return $this->error('INVOICE_DUPLICATE', [
                    'resident_id' => ['Penduduk ini sudah memiliki invoice untuk ' . $invoiceDate->format('F Y')]
                ]);
            }
        }

        // Recalculate total if any amount fields are updated
        if (isset($data['iuran_amount']) || isset($data['peturunan_amount']) || isset($data['dedosan_amount'])) {
            $data['total_amount'] =
                ($data['iuran_amount'] ?? $invoice->iuran_amount) +
                ($data['peturunan_amount'] ?? $invoice->peturunan_amount) +
                ($data['dedosan_amount'] ?? $invoice->dedosan_amount);
        }

        $invoice->update($data);

        return $this->success(new InvoiceResource($invoice->load(['resident', 'user'])));
    }
}

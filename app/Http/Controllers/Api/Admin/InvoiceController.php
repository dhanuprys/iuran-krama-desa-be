<?php

namespace App\Http\Controllers\Api\Admin;

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
    use \App\Traits\GeneratesInvoicePdf;

    // ... (existing methods)

    /**
     * Download invoice PDF.
     */
    public function download(string $id)
    {
        $invoice = Invoice::find($id);

        if (!$invoice) {
            return $this->error('RESOURCE_NOT_FOUND');
        }

        return $this->generatePdfResponse($invoice);
    }


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
            // 'resident.residentStatus', // Not strictly used in table, usually just name/nik
            // 'resident.banjar',        // Not used in table
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
    public function store(\App\Http\Requests\StoreInvoiceRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Get resident with resident status to get contribution amount
        $resident = Resident::with('residentStatus')->find($data['resident_id']);
        if (!$resident) {
            return $this->error('RESOURCE_NOT_FOUND');
        }

        if ($resident->family_status !== 'HEAD_OF_FAMILY') {
            return $this->error('VALIDATION_ERROR', ['resident_id' => ['Hanya Kepala Keluarga yang boleh memiliki tagihan.']]);
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
    public function update(\App\Http\Requests\UpdateInvoiceRequest $request, string $id): JsonResponse
    {
        $invoice = Invoice::find($id);

        if (!$invoice) {
            return $this->error('RESOURCE_NOT_FOUND');
        }

        $data = $request->validated();

        // If resident_id is being updated, get the new resident's contribution amount
        if (isset($data['resident_id'])) {
            $resident = Resident::with('residentStatus')->find($data['resident_id']);
            if (!$resident) {
                return $this->error('RESOURCE_NOT_FOUND');
            }
            if ($resident->family_status !== 'HEAD_OF_FAMILY') {
                return $this->error('VALIDATION_ERROR', ['resident_id' => ['Hanya Kepala Keluarga yang boleh memiliki tagihan.']]);
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

    /**
     * Preview bulk invoice creation
     */
    public function previewBulkCreate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'invoice_date' => 'required|date|before_or_equal:today',
            'peturunan_amount' => 'nullable|numeric|min:0',
            'dedosan_amount' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return $this->error('VALIDATION_ERROR', $validator->errors());
        }

        $invoiceDate = Carbon::parse($request->invoice_date);

        // Targeted residents: HEAD_OF_FAMILY, valid validation_status, active user
        // We only target APPROVED residents who are HEAD_OF_FAMILY
        $residents = Resident::with(['residentStatus', 'banjar'])
            ->where('family_status', 'HEAD_OF_FAMILY')
            ->where('validation_status', 'APPROVED')
            ->get();

        $previewData = [];
        $peturunan = $request->peturunan_amount ?? 0;
        $dedosan = $request->dedosan_amount ?? 0;

        foreach ($residents as $resident) {
            // Check for duplicate
            if ($this->hasDuplicateMonthlyInvoice($resident->id, $invoiceDate)) {
                continue; // Skip if already exists
            }

            // Ensure resident has status and contribution
            if (!$resident->residentStatus || !$resident->residentStatus->contribution_amount) {
                // Optionally skip or include with warning. For bulk, safer to skip or flag.
                // We'll skip for now if they don't have a defined contribution amount.
                continue;
            }

            $iuran = $resident->residentStatus->contribution_amount;
            $total = $iuran + $peturunan + $dedosan;

            $previewData[] = [
                'resident_id' => $resident->id,
                'resident_name' => $resident->name,
                'resident_nik' => $resident->nik,
                'banjar_name' => $resident->banjar->name ?? '-',
                'iuran_amount' => $iuran,
                'peturunan_amount' => $peturunan,
                'dedosan_amount' => $dedosan,
                'total_amount' => $total,
            ];
        }

        return $this->success([
            'invoice_date' => $invoiceDate->format('Y-m-d'),
            'total_residents' => count($previewData),
            'total_amount_all' => collect($previewData)->sum('total_amount'),
            'items' => $previewData
        ]);
    }

    /**
     * Store bulk invoices
     */
    public function bulkStore(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'invoice_date' => 'required|date|before_or_equal:today',
            'items' => 'required|array|min:1',
            'items.*.resident_id' => 'required|integer|exists:residents,id',
            'items.*.peturunan_amount' => 'required|numeric|min:0',
            'items.*.dedosan_amount' => 'required|numeric|min:0',
            'items.*.iuran_amount' => 'required|numeric|min:0', // Trust client or re-verify?
            // Re-verifying is safer but slower. We'll trust the checked preview data but re-check duplication.
        ]);

        if ($validator->fails()) {
            return $this->error('VALIDATION_ERROR', $validator->errors());
        }

        $data = $validator->validated();
        $invoiceDate = Carbon::parse($data['invoice_date']);
        $userId = auth()->id();
        $createdCount = 0;

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            foreach ($data['items'] as $item) {
                // Double check duplication to be safe
                if ($this->hasDuplicateMonthlyInvoice($item['resident_id'], $invoiceDate)) {
                    continue;
                }

                $total = $item['iuran_amount'] + $item['peturunan_amount'] + $item['dedosan_amount'];

                Invoice::create([
                    'resident_id' => $item['resident_id'],
                    'invoice_date' => $invoiceDate->format('Y-m-d'),
                    'iuran_amount' => $item['iuran_amount'],
                    'peturunan_amount' => $item['peturunan_amount'],
                    'dedosan_amount' => $item['dedosan_amount'],
                    'total_amount' => $total,
                    'user_id' => $userId,
                ]);
                $createdCount++;
            }
            \Illuminate\Support\Facades\DB::commit();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return $this->error('SERVER_ERROR', $e->getMessage());
        }

        return $this->success([
            'message' => "Berhasil membuat $createdCount tagihan.",
            'count' => $createdCount
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $invoice = Invoice::find($id);

        if (!$invoice) {
            return $this->error('RESOURCE_NOT_FOUND');
        }

        $invoice->delete();

        return $this->success(null);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ResidentResource;
use App\Models\Resident;
use App\Models\Banjar;
use App\Models\ResidentStatus;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ResidentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Resident::with(['residentStatus', 'banjar', 'invoices']);

        // Search by name, NIK, or phone
        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('nik', 'like', '%' . $searchTerm . '%')
                  ->orWhere('phone', 'like', '%' . $searchTerm . '%');
            });
        }

        // Filter by banjar
        if ($request->has('banjar_id')) {
            $query->where('banjar_id', $request->banjar_id);
        }

        // Filter by resident status
        if ($request->has('resident_status_id')) {
            $query->where('resident_status_id', $request->resident_status_id);
        }

        // Filter by gender
        if ($request->has('gender')) {
            $query->where('gender', $request->gender);
        }

        // Sort options
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        $allowedSortFields = ['name', 'nik', 'created_at', 'updated_at'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $residents = $query->paginate(15);

        return response()->json([
            'success' => true,
            'data' => ResidentResource::collection($residents),
            'pagination' => [
                'current_page' => $residents->currentPage(),
                'last_page' => $residents->lastPage(),
                'per_page' => $residents->perPage(),
                'total' => $residents->total(),
            ],
            'filters' => [
                'banjars' => Banjar::select('id', 'name')->get(),
                'resident_statuses' => ResidentStatus::select('id', 'name')->get(),
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nik' => 'required|string|max:30|unique:residents,nik',
            'name' => 'required|string|max:255',
            'gender' => 'required|in:L,P',
            'resident_status_id' => 'required|exists:resident_statuses,id',
            'banjar_id' => 'required|exists:banjars,id',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
        ], [
            'nik.required' => 'NIK wajib diisi.',
            'nik.unique' => 'NIK sudah terdaftar.',
            'nik.max' => 'NIK tidak boleh lebih dari 30 karakter.',
            'name.required' => 'Nama wajib diisi.',
            'name.max' => 'Nama tidak boleh lebih dari 255 karakter.',
            'gender.required' => 'Jenis kelamin wajib diisi.',
            'gender.in' => 'Jenis kelamin harus L atau P.',
            'resident_status_id.required' => 'Status penduduk wajib diisi.',
            'resident_status_id.exists' => 'Status penduduk yang dipilih tidak ditemukan.',
            'banjar_id.required' => 'Banjar wajib diisi.',
            'banjar_id.exists' => 'Banjar yang dipilih tidak ditemukan.',
            'address.max' => 'Alamat tidak boleh lebih dari 255 karakter.',
            'phone.max' => 'Nomor telepon tidak boleh lebih dari 20 karakter.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan validasi',
                'errors' => $validator->errors()
            ], 422);
        }

        $resident = Resident::create($validator->validated());

        return response()->json([
            'success' => true,
                'message' => 'Penduduk berhasil dibuat',
            'data' => new ResidentResource($resident->load(['residentStatus', 'banjar']))
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $resident = Resident::with(['residentStatus', 'banjar', 'invoices.payments'])->find($id);

        if (!$resident) {
            return response()->json([
                'success' => false,
                'message' => 'Penduduk tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new ResidentResource($resident)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $resident = Resident::find($id);

        if (!$resident) {
            return response()->json([
                'success' => false,
                'message' => 'Penduduk tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nik' => 'sometimes|string|max:30|unique:residents,nik,' . $id,
            'name' => 'sometimes|string|max:255',
            'gender' => 'sometimes|in:L,P',
            'resident_status_id' => 'sometimes|exists:resident_statuses,id',
            'banjar_id' => 'sometimes|exists:banjars,id',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
        ], [
            'nik.unique' => 'NIK sudah terdaftar.',
            'nik.max' => 'NIK tidak boleh lebih dari 30 karakter.',
            'name.max' => 'Nama tidak boleh lebih dari 255 karakter.',
            'gender.in' => 'Jenis kelamin harus L atau P.',
            'resident_status_id.exists' => 'Status penduduk yang dipilih tidak ditemukan.',
            'banjar_id.exists' => 'Banjar yang dipilih tidak ditemukan.',
            'address.max' => 'Alamat tidak boleh lebih dari 255 karakter.',
            'phone.max' => 'Nomor telepon tidak boleh lebih dari 20 karakter.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan validasi',
                'errors' => $validator->errors()
            ], 422);
        }

        $resident->update($validator->validated());

        return response()->json([
            'success' => true,
                'message' => 'Penduduk berhasil diperbarui',
            'data' => new ResidentResource($resident->load(['residentStatus', 'banjar']))
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $resident = Resident::find($id);

        if (!$resident) {
            return response()->json([
                'success' => false,
                'message' => 'Penduduk tidak ditemukan'
            ], 404);
        }

        // Check if resident has invoices
        if ($resident->invoices()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menghapus penduduk yang memiliki invoice. Hapus invoice terlebih dahulu.'
            ], 422);
        }

        $resident->delete();

        return response()->json([
            'success' => true,
                'message' => 'Penduduk berhasil dihapus'
        ]);
    }

}

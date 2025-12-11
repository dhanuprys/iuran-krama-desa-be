<?php

namespace App\Http\Controllers\Api\Operator;

use App\Http\Controllers\Controller;
use App\Http\Resources\ResidentResource;
use App\Models\Resident;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ResidentController extends Controller
{
    use \App\Traits\ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Resident::query();

        // Select specific columns to reduce payload
        $query->select([
            'residents.id',
            'residents.nik',
            'residents.name',
            'residents.gender',
            'residents.validation_status',
            'residents.banjar_id',
            'residents.resident_status_id',
            'residents.family_status',
            'residents.residential_address',
            'residents.rt_number',
            'residents.residence_name',
            'residents.house_number',
            'residents.resident_photo',
            'residents.created_at',
            'residents.updated_at'
        ]);

        $query->with([
            'residentStatus:id,name,contribution_amount',
            'banjar:id,name'
        ]);

        // Search by name, NIK, or phone
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('residents.name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('residents.nik', 'like', '%' . $searchTerm . '%')
                    ->orWhere('residents.phone', 'like', '%' . $searchTerm . '%');
            });
        }

        // Filter by banjar
        if ($request->filled('banjar_id')) {
            $query->where('residents.banjar_id', $request->banjar_id);
        }

        // Filter by resident status
        if ($request->filled('resident_status_id')) {
            $query->where('residents.resident_status_id', $request->resident_status_id);
        }

        // Filter by gender
        if ($request->filled('gender')) {
            $query->where('residents.gender', $request->gender);
        }

        // Filter by validation status
        if ($request->filled('validation_status')) {
            $query->where('residents.validation_status', $request->validation_status);
        }

        // Filter by family status
        if ($request->filled('family_status')) {
            $query->where('residents.family_status', $request->family_status);
        }

        // Sort options
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        $allowedSortFields = ['name', 'nik', 'created_at', 'updated_at'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy('residents.' . $sortBy, $sortOrder);
        }

        $residents = $query->paginate(15);

        return $this->paginated(ResidentResource::collection($residents));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nik' => 'required|string|max:16|unique:residents,nik',
            'user_id' => 'required|exists:users,id',
            'banjar_id' => 'nullable|exists:banjars,id',
            'family_card_number' => 'required|string|max:16',
            'name' => 'required|string|max:80',
            'gender' => 'required|in:L,P',
            'place_of_birth' => 'required|string|max:50',
            'date_of_birth' => 'required|date',
            'family_status' => 'required|in:HEAD_OF_FAMILY,PARENT,HUSBAND,WIFE,CHILD',
            'religion' => 'nullable|string|max:50',
            'education' => 'nullable|string|max:50',
            'work_type' => 'nullable|string|max:50',
            'marital_status' => 'nullable|in:MARRIED,SINGLE,DEAD_DIVORCE,LIVING_DIVORCE',
            'origin_address' => 'nullable|string',
            'residential_address' => 'nullable|string',
            'rt_number' => 'nullable|string|max:10',
            'residence_name' => 'nullable|string|max:100',
            'house_number' => 'nullable|string|max:20',
            'location' => 'nullable|array',
            'arrival_date' => 'nullable|date',
            'phone' => 'nullable|string|max:12',
            'email' => 'nullable|email|max:50',
            'validation_status' => 'nullable|in:PENDING,APPROVED,REJECTED',
            'photo_house' => 'nullable|image|max:5120',
            'resident_photo' => 'nullable|image|max:5120',
            'photo_ktp' => 'nullable|image|max:5120',
            'resident_status_id' => 'nullable|exists:resident_statuses,id',
        ]);

        if ($validator->fails()) {
            return $this->error('VALIDATION_ERROR', $validator->errors());
        }

        $data = $validator->validated();

        // Handle file uploads
        if ($request->hasFile('photo_house')) {
            $data['photo_house'] = $request->file('photo_house')->store('resident_photos', 'public');
        }
        if ($request->hasFile('resident_photo')) {
            $data['resident_photo'] = $request->file('resident_photo')->store('resident_photos', 'public');
        }
        if ($request->hasFile('photo_ktp')) {
            $data['photo_ktp'] = $request->file('photo_ktp')->store('resident_photos', 'public');
        }

        $resident = Resident::create($data);

        return $this->success(new ResidentResource($resident->load(['residentStatus', 'banjar'])), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $resident = Resident::with(['residentStatus', 'banjar', 'invoices.payments'])->find($id);

        if (!$resident) {
            return $this->error('RESOURCE_NOT_FOUND');
        }

        return $this->success(new ResidentResource($resident));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $resident = Resident::find($id);

        if (!$resident) {
            return $this->error('RESOURCE_NOT_FOUND');
        }

        $validator = Validator::make($request->all(), [
            'nik' => 'sometimes|string|max:16|unique:residents,nik,' . $id,
            'user_id' => 'sometimes|exists:users,id',
            'banjar_id' => 'nullable|exists:banjars,id',
            'family_card_number' => 'sometimes|string|max:16',
            'name' => 'sometimes|string|max:80',
            'gender' => 'sometimes|in:L,P',
            'place_of_birth' => 'sometimes|string|max:50',
            'date_of_birth' => 'sometimes|date',
            'family_status' => 'sometimes|in:HEAD_OF_FAMILY,PARENT,HUSBAND,WIFE,CHILD',
            'religion' => 'nullable|string|max:50',
            'education' => 'nullable|string|max:50',
            'work_type' => 'nullable|string|max:50',
            'marital_status' => 'nullable|in:MARRIED,SINGLE,DEAD_DIVORCE,LIVING_DIVORCE',
            'origin_address' => 'nullable|string',
            'residential_address' => 'nullable|string',
            'rt_number' => 'nullable|string|max:10',
            'residence_name' => 'nullable|string|max:100',
            'house_number' => 'nullable|string|max:20',
            'location' => 'nullable|array',
            'arrival_date' => 'nullable|date',
            'phone' => 'nullable|string|max:12',
            'email' => 'nullable|email|max:50',
            'validation_status' => 'nullable|in:PENDING,APPROVED,REJECTED',
            'photo_house' => 'nullable|image|max:5120',
            'resident_photo' => 'nullable|image|max:5120',
            'photo_ktp' => 'nullable|image|max:5120',
            'resident_status_id' => 'nullable|exists:resident_statuses,id',
        ]);

        if ($validator->fails()) {
            return $this->error('VALIDATION_ERROR', $validator->errors());
        }

        $data = $validator->validated();

        // Handle file uploads
        if ($request->hasFile('photo_house')) {
            $data['photo_house'] = $request->file('photo_house')->store('resident_photos', 'public');
        }
        if ($request->hasFile('resident_photo')) {
            $data['resident_photo'] = $request->file('resident_photo')->store('resident_photos', 'public');
        }
        if ($request->hasFile('photo_ktp')) {
            $data['photo_ktp'] = $request->file('photo_ktp')->store('resident_photos', 'public');
        }

        $resident->update($data);

        return $this->success(new ResidentResource($resident->load(['residentStatus', 'banjar'])));
    }
}

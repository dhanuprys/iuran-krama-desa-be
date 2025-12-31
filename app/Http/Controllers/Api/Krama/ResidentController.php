<?php

namespace App\Http\Controllers\Api\Krama;

use App\Http\Controllers\Controller;
use App\Http\Resources\ResidentResource;
use App\Models\Resident;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ResidentController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource (My Residents).
     */
    public function index(Request $request): JsonResponse
    {
        $query = Resident::query();

        $query->select([
            'residents.id',
            'residents.nik',
            'residents.name',
            'residents.gender',
            'residents.validation_status',
            'residents.family_status',
            'residents.residential_address',
            'residents.rt_number',
            'residents.residence_name',
            'residents.house_number',
            'residents.resident_photo',
            'residents.banjar_id',
            'residents.user_id',
            'residents.created_at'
        ]);

        $query->with(['residentStatus:id,name', 'banjar:id,name'])
            ->where('residents.user_id', auth()->id());

        $residents = $query->latest()->paginate(15);

        return $this->paginated(ResidentResource::collection($residents));
    }

    /**
     * Get list of residents for context switching (No Pagination).
     */
    public function context(Request $request): JsonResponse
    {
        $residents = Resident::where('user_id', auth()->id())
            ->where('validation_status', 'APPROVED')
            ->select(['id', 'name', 'nik', 'resident_photo'])
            ->get();

        return $this->success(\App\Http\Resources\ResidentContextResource::collection($residents));
    }

    /**
     * Store a newly created resource in storage (Apply for new resident).
     */
    public function store(Request $request): JsonResponse
    {
        if (!$request->user()->can_create_resident) {
            return $this->error('FORBIDDEN', null, 'Anda tidak memiliki izin untuk membuat data penduduk.', 403);
        }

        $validator = Validator::make($request->all(), [
            'nik' => 'required|string|max:16|unique:residents,nik',
            'family_card_number' => 'required|string|max:16',
            'name' => 'required|string|max:80',
            'gender' => 'required|in:L,P',
            'place_of_birth' => 'required|string|max:50',
            'date_of_birth' => 'required|date',
            'family_status' => 'required|in:HEAD_OF_FAMILY,PARENT,HUSBAND,WIFE,CHILD',
            'religion' => 'nullable|string|max:50',
            'education' => 'required_if:family_status,HEAD_OF_FAMILY|string|max:50',
            'work_type' => 'required_if:family_status,HEAD_OF_FAMILY|string|max:50',
            'marital_status' => 'required_if:family_status,HEAD_OF_FAMILY|in:MARRIED,SINGLE,DEAD_DIVORCE,LIVING_DIVORCE',
            'origin_address' => 'required_if:family_status,HEAD_OF_FAMILY|string',
            'residential_address' => 'required_if:family_status,HEAD_OF_FAMILY|string',
            'rt_number' => 'required_if:family_status,HEAD_OF_FAMILY|string|max:10',
            'residence_name' => 'required_if:family_status,HEAD_OF_FAMILY|string|max:100',
            'house_number' => 'required_if:family_status,HEAD_OF_FAMILY|string|max:20',
            'location' => 'required_if:family_status,HEAD_OF_FAMILY|array',
            'arrival_date' => 'required_if:family_status,HEAD_OF_FAMILY|date',
            'phone' => 'required_if:family_status,HEAD_OF_FAMILY|string|max:12',
            'email' => 'required_if:family_status,HEAD_OF_FAMILY|email|max:50',
            'photo_house' => 'required_if:family_status,HEAD_OF_FAMILY|image|max:5120',
            'resident_photo' => 'required_if:family_status,HEAD_OF_FAMILY|image|max:5120',
            'photo_ktp' => 'required_if:family_status,HEAD_OF_FAMILY|image|max:5120',
            'banjar_id' => 'required_if:family_status,HEAD_OF_FAMILY|exists:banjars,id',
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

        $resident = Resident::create([
            ...$data,
            'user_id' => auth()->id(), // Owned by the applier initially
            'created_by_user_id' => auth()->id(), // Track original applier
            'validation_status' => 'PENDING', // Default to pending
        ]);

        return $this->success(new ResidentResource($resident), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $user = $request->user();
        $resident = $user->residents()->with(['residentStatus', 'banjar'])->where('id', $id)->first();

        if (!$resident) {
            return $this->error('NOT_FOUND', null, 'Resident not found or unauthorized', 404);
        }

        // Allow Krama to view their own residents regardless of status (e.g. for editing pending ones)
        // if ($resident->validation_status !== 'APPROVED') {
        //     return $this->error('FORBIDDEN', null, 'Resident application is not yet approved.', 403);
        // }

        return $this->success(new ResidentResource($resident));
    }

    public function update(Request $request, string $id)
    {
        $user = $request->user();
        $resident = $user->residents()->where('id', $id)->first();

        if (!$resident) {
            return $this->error('NOT_FOUND', null, 'Resident not found or unauthorized', 404);
        }

        if (!in_array($resident->validation_status, ['PENDING', 'REJECTED'])) {
            return $this->error('VALIDATION_ERROR', null, 'Only pending or rejected applications can be updated', 422);
        }

        $validator = Validator::make($request->all(), [
            'nik' => 'required|string|max:16|unique:residents,nik,' . $id,
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
            'photo_house' => 'nullable|image|max:5120',
            'resident_photo' => 'nullable|image|max:5120',
            'photo_ktp' => 'nullable|image|max:5120',
            'banjar_id' => 'nullable|exists:banjars,id',
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

        $resident->update([
            ...$data,
            'validation_status' => 'PENDING',
            'rejection_reason' => null,
        ]);

        return $this->success(new ResidentResource($resident));
    }
}

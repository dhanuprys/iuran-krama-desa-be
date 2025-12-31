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
    public function store(\App\Http\Requests\StoreResidentRequest $request): JsonResponse
    {
        if (!$request->user()->can_create_resident) {
            return $this->error('FORBIDDEN', null, 'Anda tidak memiliki izin untuk membuat data penduduk.', 403);
        }

        $data = $request->validated();

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

    public function update(\App\Http\Requests\UpdateResidentRequest $request, string $id)
    {
        $user = $request->user();
        $resident = $user->residents()->where('id', $id)->first();

        if (!$resident) {
            return $this->error('NOT_FOUND', null, 'Resident not found or unauthorized', 404);
        }

        if (!in_array($resident->validation_status, ['PENDING', 'REJECTED'])) {
            return $this->error('VALIDATION_ERROR', null, 'Only pending or rejected applications can be updated', 422);
        }

        $data = $request->validated();

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

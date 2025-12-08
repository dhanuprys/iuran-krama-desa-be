<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Family;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FamilyController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Family::with('headOfFamily');

        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where('family_card_number', 'like', '%' . $searchTerm . '%')
                ->orWhereHas('headOfFamily', function ($q) use ($searchTerm) {
                    $q->where('name', 'like', '%' . $searchTerm . '%');
                });
        }

        $families = $query->paginate(15);

        return $this->paginated($families);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $family_card_number): JsonResponse
    {
        $family = Family::with('members.residentStatus', 'members.banjar')->find($family_card_number);

        if (!$family) {
            return $this->error('NOT_FOUND', null, 'Family not found', 404);
        }

        return $this->success($family);
    }
}

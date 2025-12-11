<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ResidentStatus;
use Illuminate\Http\Request;

class ResidentStatusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $statuses = ResidentStatus::select('id', 'name', 'contribution_amount')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Resident statuses retrieved successfully.',
            'data' => $statuses
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contribution_amount' => 'required|numeric|min:0',
        ]);

        $status = ResidentStatus::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Resident status created successfully.',
            'data' => $status
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(ResidentStatus $residentStatus)
    {
        return response()->json([
            'success' => true,
            'message' => 'Resident status retrieved successfully.',
            'data' => $residentStatus
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ResidentStatus $residentStatus)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'contribution_amount' => 'sometimes|required|numeric|min:0',
        ]);

        $residentStatus->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Resident status updated successfully.',
            'data' => $residentStatus
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ResidentStatus $residentStatus)
    {
        $residentStatus->delete();

        return response()->json([
            'success' => true,
            'message' => 'Resident status deleted successfully.'
        ]);
    }
}

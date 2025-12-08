<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AnnouncementController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = Announcement::with('creator');

        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $announcements = $query->latest()->paginate(15);

        return $this->paginated($announcements);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return $this->error('VALIDATION_ERROR', $validator->errors());
        }

        $announcement = Announcement::create([
            ...$validator->validated(),
            'created_by' => auth()->id(),
        ]);

        return $this->success($announcement, 201);
    }

    public function show(Announcement $announcement): JsonResponse
    {
        return $this->success($announcement->load('creator'));
    }

    public function update(Request $request, Announcement $announcement): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return $this->error('VALIDATION_ERROR', $validator->errors());
        }

        $announcement->update($validator->validated());

        return $this->success($announcement->load('creator'));
    }

    public function destroy(Announcement $announcement): JsonResponse
    {
        $announcement->delete();
        return $this->success(null);
    }
}

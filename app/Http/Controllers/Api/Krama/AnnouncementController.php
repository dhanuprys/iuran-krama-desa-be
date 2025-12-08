<?php

namespace App\Http\Controllers\Api\Krama;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $announcements = Announcement::with('creator')
            ->where('is_active', true)
            ->latest()
            ->paginate(15);

        return $this->paginated($announcements);
    }
}

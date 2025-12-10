<?php

namespace App\Http\Controllers\Api\Krama;

use App\Http\Controllers\Controller;
use App\Models\Banjar;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class BanjarController extends Controller
{
    use ApiResponse;

    /**
     * Handle the incoming request.
     */
    public function index(): JsonResponse
    {
        $banjars = Banjar::select('id', 'name')->orderBy('name')->get();
        return $this->success($banjars);
    }
}

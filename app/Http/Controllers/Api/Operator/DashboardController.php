<?php

namespace App\Http\Controllers\Api\Operator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'message' => 'Welcome to Operator Dashboard',
            'data' => [
                'role' => 'operator',
                'timestamp' => now()
            ]
        ]);
    }
}

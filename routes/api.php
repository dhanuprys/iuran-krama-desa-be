<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\ResidentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public Routes
Route::prefix('v1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // Protected Routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);

        // --- Krama Routes ---
        Route::prefix('krama')->group(function () {
            Route::get('profile', [\App\Http\Controllers\Api\AuthController::class, 'profile']);
            Route::put('profile', [\App\Http\Controllers\Api\AuthController::class, 'updateProfile']);
            Route::post('logout', [\App\Http\Controllers\Api\AuthController::class, 'logout']);
            Route::get('/residents', [\App\Http\Controllers\Api\Krama\ResidentController::class, 'index']);
            Route::post('/residents', [\App\Http\Controllers\Api\Krama\ResidentController::class, 'store']);
            Route::get('/residents/{id}', [\App\Http\Controllers\Api\Krama\ResidentController::class, 'show']);
            Route::put('/residents/{id}', [\App\Http\Controllers\Api\Krama\ResidentController::class, 'update']);

            Route::get('/invoices', [\App\Http\Controllers\Api\Krama\InvoiceController::class, 'index']);
            Route::get('/invoices/{id}', [\App\Http\Controllers\Api\Krama\InvoiceController::class, 'show']);

            Route::get('/announcements', [\App\Http\Controllers\Api\Krama\AnnouncementController::class, 'index']);
        });

        // --- Admin Routes ---
        Route::prefix('admin')->middleware('admin')->group(function () {
            Route::get('dashboard', [\App\Http\Controllers\Api\Admin\DashboardController::class, 'index']);
            Route::apiResource('residents', \App\Http\Controllers\Api\Admin\ResidentController::class);
            Route::apiResource('invoices', \App\Http\Controllers\Api\Admin\InvoiceController::class);
            Route::apiResource('announcements', \App\Http\Controllers\Api\Admin\AnnouncementController::class);
            Route::get('audit-logs', [\App\Http\Controllers\Api\Admin\AuditLogController::class, 'index']);
            Route::apiResource('users', \App\Http\Controllers\Api\Admin\UserController::class);
            Route::get('families', [\App\Http\Controllers\Api\Admin\FamilyController::class, 'index']);
            Route::get('families/{family_card_number}', [\App\Http\Controllers\Api\Admin\FamilyController::class, 'show']);
        });
    });
});
<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\ResidentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public Routes
Route::prefix('v1')->group(function () {
    Route::get('/meta', function () {
        return config('app.version');
    });
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
        Route::put('/change-password', [AuthController::class, 'changePassword']);
        Route::get('/user/has-resident', [\App\Http\Controllers\Api\AuthController::class, 'hasResident']);
        Route::get('/resident-statuses', [\App\Http\Controllers\Api\ResidentStatusController::class, 'index']);

        // --- Krama Routes ---
        Route::prefix('krama')->group(function () {
            Route::get('/residents/context', [\App\Http\Controllers\Api\Krama\ResidentController::class, 'context']);
            Route::get('/banjars', [\App\Http\Controllers\Api\Krama\BanjarController::class, 'index']);
            Route::get('/residents', [\App\Http\Controllers\Api\Krama\ResidentController::class, 'index']);
            Route::post('/residents', [\App\Http\Controllers\Api\Krama\ResidentController::class, 'store']);
            Route::get('/residents/{id}', [\App\Http\Controllers\Api\Krama\ResidentController::class, 'show']);
            Route::put('/residents/{id}', [\App\Http\Controllers\Api\Krama\ResidentController::class, 'update']);

            Route::get('/invoices', [\App\Http\Controllers\Api\Krama\InvoiceController::class, 'index']);
            Route::get('/invoices/{id}/download', [\App\Http\Controllers\Api\Krama\InvoiceController::class, 'download']);
            Route::get('/invoices/{id}', [\App\Http\Controllers\Api\Krama\InvoiceController::class, 'show']);

            Route::get('/dashboard', [\App\Http\Controllers\Api\Krama\DashboardController::class, 'index']);
            Route::get('/announcements', [\App\Http\Controllers\Api\Krama\AnnouncementController::class, 'index']);
        });

        // --- Operator Routes ---
        Route::prefix('operator')->middleware('operator')->group(function () {
            Route::get('dashboard', [\App\Http\Controllers\Api\Operator\DashboardController::class, 'index']);
            Route::get('invoices/{id}/download', [\App\Http\Controllers\Api\Operator\InvoiceController::class, 'download']);
            Route::apiResource('invoices', \App\Http\Controllers\Api\Operator\InvoiceController::class);
            Route::apiResource('payments', \App\Http\Controllers\Api\Operator\PaymentController::class);
            Route::apiResource('residents', \App\Http\Controllers\Api\Operator\ResidentController::class)->except(['destroy']);
        });

        // --- Admin Routes ---
        Route::prefix('admin')->middleware('admin')->group(function () {
            Route::get('dashboard', [\App\Http\Controllers\Api\Admin\DashboardController::class, 'index']);
            Route::apiResource('residents', \App\Http\Controllers\Api\Admin\ResidentController::class);
            Route::post('residents/{id}/validate', [\App\Http\Controllers\Api\Admin\ResidentController::class, 'validateResident']);
            Route::post('invoices/bulk-preview', [\App\Http\Controllers\Api\Admin\InvoiceController::class, 'previewBulkCreate']);
            Route::post('invoices/bulk-store', [\App\Http\Controllers\Api\Admin\InvoiceController::class, 'bulkStore']);
            Route::get('invoices/{id}/download', [\App\Http\Controllers\Api\Admin\InvoiceController::class, 'download']);
            Route::apiResource('invoices', \App\Http\Controllers\Api\Admin\InvoiceController::class);
            Route::apiResource('announcements', \App\Http\Controllers\Api\Admin\AnnouncementController::class);
            Route::apiResource('audit-logs', \App\Http\Controllers\Api\Admin\AuditLogController::class)->only(['index', 'show']);
            Route::apiResource('users', \App\Http\Controllers\Api\Admin\UserController::class);
            Route::get('families', [\App\Http\Controllers\Api\Admin\FamilyController::class, 'index']);
            Route::get('families/{family_card_number}', [\App\Http\Controllers\Api\Admin\FamilyController::class, 'show']);
            Route::apiResource('banjars', \App\Http\Controllers\Api\Admin\BanjarController::class);
            Route::apiResource('resident-statuses', \App\Http\Controllers\Api\Admin\ResidentStatusController::class);
            Route::apiResource('payments', \App\Http\Controllers\Api\Admin\PaymentController::class);
        });
    });
});
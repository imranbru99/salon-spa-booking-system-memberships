<?php

use App\Http\Controllers\Api\V1\ApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/health', function () {
        return response()->json([
            'status' => 'healthy',
            'timestamp' => now()->toIso8601String(),
            'app' => config('app.name'),
        ]);
    });

    // Auth
    Route::post('/auth/login', [ApiController::class, 'login']);

    // Business endpoints
    Route::get('/branches', [ApiController::class, 'branches']);
    Route::get('/services', [ApiController::class, 'services']);
    Route::get('/staff', [ApiController::class, 'staff']);
    Route::get('/customers', [ApiController::class, 'customers']);
    Route::get('/appointments', [ApiController::class, 'appointments']);
    Route::post('/appointments', [ApiController::class, 'createAppointment']);
    Route::get('/availability', [ApiController::class, 'checkAvailability']);

    // Commercial & Sales
    Route::get('/packages', [ApiController::class, 'packages']);
    Route::get('/memberships', [ApiController::class, 'memberships']);

    // AI & Integrations
    Route::post('/ai/assistant', [ApiController::class, 'askAIAssistant']);
    Route::post('/webhooks/payment', [ApiController::class, 'handlePaymentWebhook']);
});

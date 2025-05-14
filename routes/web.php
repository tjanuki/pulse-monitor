<?php

use App\Http\Controllers\Api\MetricsController;
use Illuminate\Support\Facades\Route;

// Web routes
Route::get('/', function () {
    return view('welcome');
});

// API routes
Route::prefix('api')->group(function () {
    // Metrics routes - protected with API key middleware
    Route::middleware('verify.node.api')->group(function () {
        Route::post('/metrics', [MetricsController::class, 'store']);
    });
});

<?php

use App\Http\Controllers\Api\MetricsController;
use App\Http\Controllers\Api\StatusNodesController;
use App\Http\Controllers\Api\ThresholdConfigurationsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Metrics routes - protected with API key middleware
Route::middleware('verify.node.api')->group(function () {
    Route::post('/metrics', [MetricsController::class, 'store']);
    Route::post('/metrics/batch', [MetricsController::class, 'storeBatch']);
});

// Status node management - protected with auth middleware
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('nodes', StatusNodesController::class);
    Route::post('/nodes/{id}/regenerate-key', [StatusNodesController::class, 'regenerateApiKey']);
    Route::apiResource('thresholds', ThresholdConfigurationsController::class);
});
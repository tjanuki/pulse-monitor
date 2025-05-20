<?php

use App\Http\Controllers\Api\MetricsController;
use App\Http\Controllers\Api\StatusNodesController;
use App\Http\Controllers\Api\ThresholdConfigurationsController;
use App\Http\Controllers\Api\AlertsController;
use App\Http\Controllers\Api\HistoricalMetricsController;
use App\Http\Controllers\Api\RecommendationsController;
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
    // Status Nodes
    Route::apiResource('nodes', StatusNodesController::class);
    Route::post('/nodes/{id}/regenerate-key', [StatusNodesController::class, 'regenerateApiKey']);
    
    // Threshold Configurations
    Route::apiResource('thresholds', ThresholdConfigurationsController::class);
    
    // Alerts Management
    Route::get('/alerts', [AlertsController::class, 'index']);
    Route::get('/alerts/{alertId}', [AlertsController::class, 'show']);
    Route::post('/alerts/{alertId}/resolve', [AlertsController::class, 'resolve']);
    Route::get('/nodes/{nodeId}/alerts', [AlertsController::class, 'forNode']);
    
    // Historical Metrics
    Route::get('/nodes/{nodeId}/metrics/{metricName}/trend', [HistoricalMetricsController::class, 'getTrendData']);
    Route::get('/metrics/{metricName}/compare', [HistoricalMetricsController::class, 'compareNodes']);
    Route::post('/metrics/aggregate', [HistoricalMetricsController::class, 'triggerAggregation']);
    
    // Recommendations Management
    Route::apiResource('recommendations', RecommendationsController::class);
    Route::post('/recommendations/{id}/toggle', [RecommendationsController::class, 'toggleActive']);
});
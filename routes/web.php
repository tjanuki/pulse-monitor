<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Dashboard routes
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/nodes/{id}', [DashboardController::class, 'nodeDetails'])->name('nodes.details');
Route::get('/configuration/thresholds', [DashboardController::class, 'thresholdsConfig'])->name('thresholds.config');
Route::get('/trends', [DashboardController::class, 'trends'])->name('trends');

// Legacy welcome route
Route::get('/welcome', function () {
    return view('welcome');
});

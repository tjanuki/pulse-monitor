<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Dashboard routes (protected by auth)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/nodes/{id}', [DashboardController::class, 'nodeDetails'])->name('nodes.details');
    Route::get('/configuration/thresholds', [DashboardController::class, 'thresholdsConfig'])->name('thresholds.config');
    Route::get('/trends', [DashboardController::class, 'trends'])->name('trends');
    
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

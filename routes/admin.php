<?php

use App\Http\Controllers\Admin\AdminController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Admin Dashboard
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // User Management
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    
    // Verification Management
    Route::get('/verifications', [AdminController::class, 'verifications'])->name('verifications');
    Route::get('/verifications/{userId}', [AdminController::class, 'viewVerification'])->name('verifications.view');
    Route::post('/verifications/{userId}/approve', [AdminController::class, 'approveVerification'])->name('verifications.approve');
    Route::post('/verifications/{userId}/reject', [AdminController::class, 'rejectVerification'])->name('verifications.reject');
}); 
<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ReportsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Admin Dashboard
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // User Management
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/users/{userId}', [AdminController::class, 'viewUser'])->name('users.view');
    Route::delete('/users/{userId}', [AdminController::class, 'deleteUser'])->name('users.delete');
    
    // Verification Management
    Route::get('/verifications', [AdminController::class, 'verifications'])->name('verifications');
    Route::get('/verifications/{userId}', [AdminController::class, 'viewVerification'])->name('verifications.view');
    Route::post('/verifications/{userId}/approve', [AdminController::class, 'approveVerification'])->name('verifications.approve');
    Route::post('/verifications/{userId}/reject', [AdminController::class, 'rejectVerification'])->name('verifications.reject');
    
    // Reports Management - Fixed route order (stats before {id})
    Route::get('/reports', [ReportsController::class, 'index'])->name('reports.index');
    Route::get('/reports/stats', [ReportsController::class, 'getStats'])->name('reports.stats');
    Route::get('/reports/{id}', [ReportsController::class, 'show'])->name('reports.show');
    Route::put('/reports/{id}', [ReportsController::class, 'update'])->name('reports.update');
    
    // Therapist Management
    Route::resource('therapists', \App\Http\Controllers\Admin\TherapistsController::class);
    Route::get('/therapist-bookings', [\App\Http\Controllers\Admin\TherapistsController::class, 'bookings'])->name('therapists.bookings');
    Route::put('/therapist-bookings/{booking}', [\App\Http\Controllers\Admin\TherapistsController::class, 'updateBooking'])->name('therapists.bookings.update');
    
    // Subscription Management
    Route::get('/subscriptions', [AdminController::class, 'subscriptions'])->name('subscriptions');
    Route::get('/premium-users', [AdminController::class, 'getPremiumUsers'])->name('premium.users');
}); 
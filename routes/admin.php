<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ReportsController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Admin Dashboard
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // Admin Settings
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::patch('/password', [AdminController::class, 'updatePassword'])->name('password.update');
    
    // User Management
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/users/{userId}', [AdminController::class, 'viewUser'])->name('users.view');
    Route::delete('/users/{userId}', [AdminController::class, 'deleteUser'])->name('users.delete');
    Route::patch('/users/{user}/verification', [AdminController::class, 'updateUserVerification'])->name('users.verification.update');
    
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
    Route::post('/subscriptions/{user}/extend', [AdminController::class, 'extendSubscription'])->name('subscriptions.extend');
    Route::post('/subscriptions/{user}/cancel', [AdminController::class, 'cancelSubscription'])->name('subscriptions.cancel');
    Route::post('/subscriptions/{user}/reactivate', [AdminController::class, 'reactivateSubscription'])->name('subscriptions.reactivate');
    Route::post('/subscriptions/{user}/gift', [AdminController::class, 'giftSubscription'])->name('subscriptions.gift');
    Route::get('/premium-users', [AdminController::class, 'getPremiumUsers'])->name('premium.users');
    
    // AI-Powered Admin Tools
    Route::get('/ai-insights', [AdminController::class, 'aiInsights'])->name('ai.insights');
    Route::post('/ai-broadcast/generate', [AdminController::class, 'generateBroadcast'])->name('ai.generate-broadcast');
    Route::post('/ai-broadcast/send', [AdminController::class, 'sendBroadcast'])->name('ai.send-broadcast');
    Route::post('/ai-broadcast/save-draft', [AdminController::class, 'saveBroadcastDraft'])->name('ai.save-broadcast-draft');
    Route::get('/ai-broadcast/load-draft', [AdminController::class, 'loadBroadcastDraft'])->name('ai.load-broadcast-draft');
    Route::delete('/ai-broadcast/delete-draft', [AdminController::class, 'deleteBroadcastDraft'])->name('ai.delete-broadcast-draft');
    Route::post('/ai-insights/generate', [AdminController::class, 'generateUserInsights'])->name('ai.insights.generate');
    
    // Zoho Campaign Management
    Route::post('/zoho-campaign/import-users', [AdminController::class, 'importUsersToZoho'])->name('zoho.import-users');
    Route::get('/zoho-campaign/mailing-lists', [AdminController::class, 'getMailingLists'])->name('zoho.mailing-lists');
    Route::post('/zoho-campaign/create-campaign', [AdminController::class, 'createCampaign'])->name('zoho.create-campaign');
    Route::get('/zoho-campaign/stats/{campaignKey}', [AdminController::class, 'getCampaignStats'])->name('zoho.campaign-stats');
    
    // AI User Insights Chatbot
    Route::post('/ai-user-insights', [AdminController::class, 'handleUserInsights'])->name('ai.user-insights');
    Route::get('/user-insights', [AdminController::class, 'userInsightsPage'])->name('user-insights');

}); 
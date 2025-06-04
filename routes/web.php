<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Redirect root to login page
Route::get('/', function () {
    return redirect()->route('login');
});

// Dashboard with user profile data
Route::get('/dashboard', function () {
    $user = auth()->user();
    
    // If user is not verified and has no verification record, redirect to verification intro
    if (!$user->is_verified && !$user->verification) {
        return redirect()->route('verification.intro');
    }
    
    // Remove redirect to verification status for pending verifications
    // Users will now go directly to dashboard after completing verification
    
    // Calculate profile completion percentage
    $profileCompletion = 0;
    if ($user->profile) {
        $totalFields = count($user->profile->getFillable());
        $filledFields = 0;
        
        foreach ($user->profile->getFillable() as $field) {
            if (!empty($user->profile->{$field}) && $field !== 'user_id') {
                $filledFields++;
            }
        }
        
        if ($totalFields > 0) {
            $profileCompletion = round(($filledFields / ($totalFields - 1)) * 100);
        }
    }
    
    return Inertia::render('Dashboard', [
        'user' => $user,
        'profile' => $user->profile,
        'profileCompletion' => $profileCompletion,
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Basic profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Detailed profile routes
    Route::get('/profile/details', [ProfileController::class, 'editDetails'])->name('profile.edit.details');
    Route::post('/profile/details', [ProfileController::class, 'updateDetails'])->name('profile.update.details');
    
    // Matches routes (placeholder until we create a controller)
    Route::get('/matches', function() {
        return Inertia::render('Matches/Index');
    })->name('matches');
    
    // Messages routes (placeholder until we create a controller)
    Route::get('/messages', function() {
        return Inertia::render('Messages/Index');
    })->name('messages');
});

// Verification routes
Route::middleware(['auth'])->group(function () {
    Route::get('/verification', [App\Http\Controllers\VerificationController::class, 'intro'])->name('verification.intro');
    Route::get('/verification/document-type', [App\Http\Controllers\VerificationController::class, 'documentTypeSelection'])->name('verification.document-type');
    Route::get('/verification/document-upload', [App\Http\Controllers\VerificationController::class, 'documentUpload'])->name('verification.document-upload');
    Route::post('/verification', [App\Http\Controllers\VerificationController::class, 'store'])->name('verification.store');
    Route::get('/verification/complete', [App\Http\Controllers\VerificationController::class, 'complete'])->name('verification.complete');
});

// Public profile viewing
Route::get('/profile/{id}', [ProfileController::class, 'show'])->name('profile.show');

// Profile view for matches
Route::get('/matches/profile/{id}', function($id) {
    return Inertia::render('Profile/View', [
        'id' => $id
    ]);
})->middleware(['auth'])->name('profile.view');

require __DIR__.'/auth.php';

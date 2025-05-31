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

// Public profile viewing
Route::get('/profile/{id}', [ProfileController::class, 'show'])->name('profile.show');

require __DIR__.'/auth.php';

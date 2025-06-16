<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Include admin routes
require __DIR__.'/admin.php';

// Redirect root to login page
Route::get('/', function () {
    return redirect()->route('login');
});

// Dashboard with user profile data
Route::get('/dashboard', function () {
    $user = auth()->user();
    
    // Check if user is admin (based on email)
    if ($user->email === 'admin@zawagafrica.com') {
        return redirect()->route('admin.dashboard');
    }
    
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
    
    // Format the user's profile photo URL if it exists
    if ($user->profile_photo) {
        $user->profile_photo = asset('storage/' . $user->profile_photo);
    }
    
    // Get all users except the current user as potential matches
    $potentialMatches = \App\Models\User::where('id', '!=', $user->id)
        ->where('email', '!=', 'admin@zawagafrica.com') // Exclude admin user
        ->with('photos') // Include photos relationship if available
        ->take(10) // Limit to 10 matches for now
        ->get();
    
    // Format photos URLs for potential matches
    foreach ($potentialMatches as $potentialMatch) {
        if ($potentialMatch->profile_photo) {
            $potentialMatch->profile_photo = asset('storage/' . $potentialMatch->profile_photo);
        }
        
        // Format photos URLs
        if ($potentialMatch->photos) {
            foreach ($potentialMatch->photos as $photo) {
                $photo->url = asset('storage/' . $photo->photo_path);
            }
        }
    }
    
    return Inertia::render('Dashboard', [
        'user' => $user,
        'profile' => $user->profile,
        'profileCompletion' => $profileCompletion,
        'potentialMatches' => $potentialMatches,
    ]);
})->middleware(['auth', 'verified', 'verified.user'])->name('dashboard');

// Admin routes
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    // Only allow access to admin@zawagafrica.com
    Route::get('/dashboard', function () {
        if (auth()->user()->email !== 'admin@zawagafrica.com') {
            return redirect()->route('dashboard');
        }
        
        // Get some basic stats for the admin dashboard
        $totalUsers = \App\Models\User::count();
        $recentUsers = \App\Models\User::latest()->take(5)->get();
        
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'totalUsers' => $totalUsers,
            ],
            'recentUsers' => $recentUsers,
        ]);
    })->name('dashboard');
    
    // Users management
    Route::get('/users', function () {
        if (auth()->user()->email !== 'admin@zawagafrica.com') {
            return redirect()->route('dashboard');
        }
        
        $users = \App\Models\User::latest()->paginate(15);
        
        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
        ]);
    })->name('users');
    
    // Verifications management - removed and using AdminController route instead
});

Route::middleware('auth')->group(function () {
    // Basic profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Detailed profile routes
    Route::get('/profile/details', [ProfileController::class, 'editDetails'])->name('profile.edit.details');
    Route::post('/profile/details', [ProfileController::class, 'updateDetails'])->name('profile.update.details');
    
    // New Me profile routes
    Route::get('/me/profile', [App\Http\Controllers\Me\ProfileController::class, 'index'])->name('me.profile');
    
    Route::get('/me/photos', [App\Http\Controllers\Me\PhotosController::class, 'index'])->name('me.photos');
    Route::post('/me/photos/upload', [App\Http\Controllers\Me\PhotosController::class, 'upload'])->name('me.photos.upload');
    Route::delete('/me/photos/{id}', [App\Http\Controllers\Me\PhotosController::class, 'delete'])->name('me.photos.delete');
    Route::put('/me/photos/{id}/primary', [App\Http\Controllers\Me\PhotosController::class, 'setPrimary'])->name('me.photos.primary');
    
    Route::get('/me/hobbies', [App\Http\Controllers\Me\HobbiesController::class, 'index'])->name('me.hobbies');
    Route::post('/me/hobbies/update', [App\Http\Controllers\Me\HobbiesController::class, 'update'])->name('me.hobbies.update');
    
    Route::get('/me/personality', [App\Http\Controllers\Me\PersonalityController::class, 'index'])->name('me.personality');
    Route::post('/me/personality/update', [App\Http\Controllers\Me\PersonalityController::class, 'update'])->name('me.personality.update');
    
    Route::get('/me/faqs', [App\Http\Controllers\Me\FAQsController::class, 'index'])->name('me.faqs');
    Route::post('/me/faqs/update', [App\Http\Controllers\Me\FAQsController::class, 'update'])->name('me.faqs.update');
    
    // Matches routes (placeholder until we create a controller)
    Route::get('/matches', function() {
        return Inertia::render('Matches/Index');
    })->name('matches');
    
    // Messages routes
    Route::get('/messages', [App\Http\Controllers\MessageController::class, 'index'])->name('messages');
    Route::get('/messages/{id}', [App\Http\Controllers\MessageController::class, 'show'])->name('messages.show');
    Route::post('/messages', [App\Http\Controllers\MessageController::class, 'store'])->name('messages.store');
    
    // Add new routes for profile updates
    Route::post('/profile/update', [App\Http\Controllers\Me\ProfileController::class, 'update']);
    Route::post('/profile/photo-update', [App\Http\Controllers\Me\ProfileController::class, 'updatePhoto']);
});

// Verification routes
Route::middleware(['auth'])->group(function () {
    Route::get('/verification', [App\Http\Controllers\VerificationController::class, 'intro'])->name('verification.intro');
    Route::get('/verification/document-type', [App\Http\Controllers\VerificationController::class, 'documentTypeSelection'])->name('verification.document-type');
    Route::get('/verification/document-upload', [App\Http\Controllers\VerificationController::class, 'documentUpload'])->name('verification.document-upload');
    Route::post('/verification', [App\Http\Controllers\VerificationController::class, 'store'])->name('verification.store');
    Route::get('/verification/complete', [App\Http\Controllers\VerificationController::class, 'complete'])->name('verification.complete');
    Route::get('/verification/pending', [App\Http\Controllers\VerificationController::class, 'pending'])->name('verification.pending');
});

// Public profile viewing
Route::get('/profile/{id}', [ProfileController::class, 'show'])->name('profile.show');

// Profile view for matches
Route::get('/matches/profile/{id}', function($id) {
    // Get the user by ID with all related data
    $user = \App\Models\User::with([
        'photos', 
        'profile', 
        'appearance', 
        'lifestyle', 
        'background', 
        'about',
        'interests',
        'personality',
        'overview'
    ])->findOrFail($id);
    
    // Calculate compatibility (mock for now)
    $compatibility = rand(70, 99);
    
    // Format profile photo URL if it exists
    if ($user->profile_photo) {
        $user->profile_photo = asset('storage/' . $user->profile_photo);
    }
    
    // Format photos URLs
    if ($user->photos) {
        foreach ($user->photos as $photo) {
            $photo->url = asset('storage/' . $photo->photo_path);
        }
    }
    
    return Inertia::render('Profile/View', [
        'id' => $id,
        'userData' => $user,
        'compatibility' => $compatibility
    ]);
})->middleware(['auth'])->name('profile.view');

// Test route for debugging
Route::get('/admin/test-verification', function () {
    return Inertia::render('Admin/TestVerification');
})->middleware(['auth'])->name('admin.test-verification');

// Subscription routes
Route::middleware(['auth'])->group(function () {
    Route::get('/subscription', [App\Http\Controllers\SubscriptionController::class, 'index'])->name('subscription');
    Route::post('/subscription/purchase', [App\Http\Controllers\SubscriptionController::class, 'purchase'])->name('subscription.purchase');
});

require __DIR__.'/auth.php';

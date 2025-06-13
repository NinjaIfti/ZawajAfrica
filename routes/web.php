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
    
    return Inertia::render('Dashboard', [
        'user' => $user,
        'profile' => $user->profile,
        'profileCompletion' => $profileCompletion,
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
    
    // Verifications management
    Route::get('/verifications', function () {
        if (auth()->user()->email !== 'admin@zawagafrica.com') {
            return redirect()->route('dashboard');
        }
        
        $pendingVerifications = \App\Models\User::whereHas('verification', function($query) {
            $query->where('status', 'pending');
        })->with('verification')->paginate(15);
        
        return Inertia::render('Admin/Verifications/Index', [
            'pendingVerifications' => $pendingVerifications,
        ]);
    })->name('verifications');
});

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
    Route::get('/verification/pending', [App\Http\Controllers\VerificationController::class, 'pending'])->name('verification.pending');
});

// Public profile viewing
Route::get('/profile/{id}', [ProfileController::class, 'show'])->name('profile.show');

// Profile view for matches
Route::get('/matches/profile/{id}', function($id) {
    return Inertia::render('Profile/View', [
        'id' => $id
    ]);
})->middleware(['auth'])->name('profile.view');

// Test route for debugging
Route::get('/admin/test-verification', function () {
    return Inertia::render('Admin/TestVerification');
})->middleware(['auth'])->name('admin.test-verification');

// Debug route for verification statuses
Route::get('/admin/debug-verifications', function () {
    if (auth()->user()->email !== 'admin@zawagafrica.com') {
        return redirect()->route('dashboard');
    }
    
    $verifications = \DB::table('verifications')
        ->select('verifications.*', 'users.name', 'users.email')
        ->join('users', 'users.id', '=', 'verifications.user_id')
        ->get();
    
    return response()->json([
        'verifications' => $verifications,
        'counts' => [
            'total' => $verifications->count(),
            'pending' => $verifications->where('status', 'pending')->count(),
            'approved' => $verifications->where('status', 'approved')->count(),
            'rejected' => $verifications->where('status', 'rejected')->count(),
        ]
    ]);
})->middleware(['auth'])->name('admin.debug-verifications');

// Fix approved verifications route
Route::get('/admin/fix-verifications', function () {
    if (auth()->user()->email !== 'admin@zawagafrica.com') {
        return redirect()->route('dashboard');
    }
    
    // Get all verifications
    $verifications = \DB::table('verifications')->get();
    
    $output = [
        'before' => [
            'total' => $verifications->count(),
            'pending' => $verifications->where('status', 'pending')->count(),
            'approved' => $verifications->where('status', 'approved')->count(),
            'rejected' => $verifications->where('status', 'rejected')->count(),
        ],
        'fixed' => []
    ];
    
    // Check for users with is_verified=true but verification status not approved
    $usersToFix = \DB::table('users')
        ->join('verifications', 'users.id', '=', 'verifications.user_id')
        ->where('users.is_verified', true)
        ->where('verifications.status', '!=', 'approved')
        ->select('users.id', 'users.name', 'users.email', 'verifications.status')
        ->get();
        
    foreach ($usersToFix as $user) {
        \DB::table('verifications')
            ->where('user_id', $user->id)
            ->update([
                'status' => 'approved',
                'verified_at' => now()
            ]);
            
        $output['fixed'][] = [
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'old_status' => $user->status,
            'new_status' => 'approved'
        ];
    }
    
    // Get updated counts
    $updatedVerifications = \DB::table('verifications')->get();
    
    $output['after'] = [
        'total' => $updatedVerifications->count(),
        'pending' => $updatedVerifications->where('status', 'pending')->count(),
        'approved' => $updatedVerifications->where('status', 'approved')->count(),
        'rejected' => $updatedVerifications->where('status', 'rejected')->count(),
    ];
    
    return response()->json($output);
})->middleware(['auth'])->name('admin.fix-verifications');

require __DIR__.'/auth.php';

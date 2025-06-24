<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MatchController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Http\Middleware\TierAccessMiddleware;
use Illuminate\Http\Request;

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
    
    // Get potential matches using the MatchingService for real compatibility scores
    $matchingService = app(\App\Services\MatchingService::class);
    $matchResults = $matchingService->getMatches($user, [], 10);
    $potentialMatches = $matchResults['matches']; // Use the formatted matches directly
    
    // Get active therapists for the widget
    $therapists = \App\Models\Therapist::where('status', 'active')
        ->take(3)
        ->get()
        ->map(function ($therapist) {
            $therapist->photo_url = $therapist->photo_url;
            return $therapist;
        });
    
    
    // Get tier information
    $tierService = app(App\Services\UserTierService::class);
    $userTier = $tierService->getUserTier($user);
    $tierInfo = $tierService->getTierInfo($userTier);
    $dailyUsage = [
        'profile_views' => $tierService->canViewProfile($user),
        'messages' => $tierService->canSendMessage($user),
    ];

    return Inertia::render('Dashboard', [
        'user' => $user->load('profile'), // Ensure profile is loaded
        'profile' => $user->profile,
        'profileCompletion' => $profileCompletion,
        'potentialMatches' => $potentialMatches,
        'therapists' => $therapists,
        'tierInfo' => $tierInfo,
        'dailyUsage' => $dailyUsage,
        'userTier' => $userTier, // Pass the calculated tier directly
    ]);
})->middleware(['auth', 'verified', 'verified.user'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Basic profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Detailed profile routes
    Route::get('/profile/details', [ProfileController::class, 'editDetails'])->name('profile.edit.details');
    Route::post('/profile/details', [ProfileController::class, 'updateDetails'])->name('profile.update.details');
    
    // User reporting routes
    Route::post('/reports', [App\Http\Controllers\ReportController::class, 'store'])->name('reports.store');
    Route::post('/reports/block', [App\Http\Controllers\ReportController::class, 'block'])->name('reports.block');

    // Therapist routes
    Route::get('/therapists', [App\Http\Controllers\TherapistBookingController::class, 'index'])->name('therapists.index');
    Route::get('/therapists/{id}', [App\Http\Controllers\TherapistBookingController::class, 'show'])->name('therapists.show');
    Route::post('/therapists/book', [App\Http\Controllers\TherapistBookingController::class, 'store'])->name('therapists.book');
    
    // Therapist booking management routes
    Route::get('/my-bookings', [App\Http\Controllers\TherapistBookingController::class, 'userBookings'])->name('therapists.bookings');
    Route::put('/bookings/{id}/cancel', [App\Http\Controllers\TherapistBookingController::class, 'cancel'])->name('therapists.bookings.cancel');
    
    // Therapist booking reminder routes (for admin/system use)
    Route::post('/therapist-bookings/reminders/{type}', [App\Http\Controllers\TherapistBookingController::class, 'sendReminders'])
        ->where('type', '24h|1h|15m')
        ->name('bookings.reminders');
    
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
    
    // Matches routes (API only - no index page)
    Route::post('/matches/pass', [App\Http\Controllers\MatchController::class, 'pass'])->name('matches.pass');
    Route::get('/matches/filters', [App\Http\Controllers\MatchController::class, 'getFilters'])->name('matches.filters');
    Route::get('/matches/ai-suggestions', [App\Http\Controllers\MatchController::class, 'getAISuggestions'])->name('matches.ai-suggestions');
    
    // Messages routes
    Route::get('/messages', [App\Http\Controllers\MessageController::class, 'index'])->name('messages');
    Route::get('/messages/{id}', [App\Http\Controllers\MessageController::class, 'show'])->name('messages.show');
    Route::post('/messages', [App\Http\Controllers\MessageController::class, 'store'])->name('messages.store');
    
    // Add new routes for profile updates
    Route::post('/profile/update', [App\Http\Controllers\Me\ProfileController::class, 'update']);
    Route::post('/profile/photo-update', [App\Http\Controllers\Me\ProfileController::class, 'updatePhoto']);
    
    // AI Chatbot routes
    Route::get('/chatbot', [App\Http\Controllers\ChatbotController::class, 'index'])->name('chatbot.index');
    Route::post('/chatbot/chat', [App\Http\Controllers\ChatbotController::class, 'chat'])->name('chatbot.chat');
    Route::delete('/chatbot/history', [App\Http\Controllers\ChatbotController::class, 'clearHistory'])->name('chatbot.clear');
    Route::get('/chatbot/history', [App\Http\Controllers\ChatbotController::class, 'getHistory'])->name('chatbot.history');
    Route::get('/chatbot/starters', [App\Http\Controllers\ChatbotController::class, 'getStarters'])->name('chatbot.starters');
    Route::post('/chatbot/preferences', [App\Http\Controllers\ChatbotController::class, 'updatePreferences'])->name('chatbot.preferences.update');
    Route::get('/chatbot/preferences', [App\Http\Controllers\ChatbotController::class, 'getPreferences'])->name('chatbot.preferences');
    Route::get('/chatbot/status', [App\Http\Controllers\ChatbotController::class, 'status'])->name('chatbot.status');
    
    // Notification routes
    Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'page'])->name('notifications.index');
    Route::get('/notifications/data', [App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.data');
    Route::get('/notifications/unread', [App\Http\Controllers\NotificationController::class, 'unread'])->name('notifications.unread');
    Route::patch('/notifications/{id}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::patch('/notifications/mark-all-read', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::delete('/notifications/{id}', [App\Http\Controllers\NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::delete('/notifications/clear-read', [App\Http\Controllers\NotificationController::class, 'clearRead'])->name('notifications.clear-read');
    Route::get('/notifications/settings', [App\Http\Controllers\NotificationController::class, 'getSettings'])->name('notifications.settings.get');
    Route::post('/notifications/settings', [App\Http\Controllers\NotificationController::class, 'updateSettings'])->name('notifications.settings.update');

    // Tier usage API
    Route::get('/api/tier-usage', function () {
        $user = Auth::user();
        $tierService = app(\App\Services\UserTierService::class);
        
        $tier = $tierService->getUserTier($user);
        $tierInfo = $tierService->getTierInfo($tier);
        $limits = $tierService->getUserLimits($user);
        $dailyUsage = $tierService->getDailyUsageSummary($user);
        
        // Get profile view status
        $profileViewStatus = $tierService->canViewProfile($user);
        
        // Get messaging status
        $messagingStatus = $tierService->canSendMessage($user);
        
        return response()->json([
            'tier' => $tier,
            'tier_info' => $tierInfo,
            'limits' => $limits,
            'daily_usage' => [
                'profile_views' => $profileViewStatus,
                'messages' => $messagingStatus
            ],
            'today_count' => [
                'profile_views' => $tierService->getTodayCount($user, 'profile_views'),
                'messages_sent' => $tierService->getTodayCount($user, 'messages_sent')
            ],
            'upgrade_suggestions' => $tierService->getUpgradeSuggestions($user)
        ]);
    })->name('api.tier-usage');

    // KYC Routes for Monnify verification
    Route::prefix('kyc')->name('kyc.')->group(function () {
        Route::get('/', [App\Http\Controllers\KycController::class, 'index'])->name('index');
        Route::post('/bvn', [App\Http\Controllers\KycController::class, 'submitBvn'])->name('submit.bvn');
        Route::post('/nin', [App\Http\Controllers\KycController::class, 'submitNin'])->name('submit.nin');
        Route::post('/both', [App\Http\Controllers\KycController::class, 'submitBoth'])->name('submit.both');
        Route::get('/status', [App\Http\Controllers\KycController::class, 'getStatus'])->name('status');
    });
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
    $currentUser = auth()->user();
    
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
        'overview',
        'others'
    ])->findOrFail($id);
    
    // Calculate real compatibility using MatchingService
    $matchingService = app(\App\Services\MatchingService::class);
    $compatibilityScore = 85; // Default fallback
    
    try {
        // Use the same scoring logic from MatchingService
        $reflection = new ReflectionClass($matchingService);
        $scoreMatches = $reflection->getMethod('scoreMatches');
        $scoreMatches->setAccessible(true);
        
        // Create a collection with just this user to calculate compatibility
        $userCollection = collect([$user]);
        $scoredUsers = $scoreMatches->invokeArgs($matchingService, [$currentUser, $userCollection]);
        
        if ($scoredUsers->isNotEmpty()) {
            $compatibilityScore = $scoredUsers->first()->compatibility_score ?? 85;
        }
    } catch (\Exception $e) {
        // Fallback to default score if calculation fails
        $compatibilityScore = 85;
    }
    
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
    
    // Get user tier information
    $tierService = app(\App\Services\UserTierService::class);
    $userTier = $tierService->getUserTier($currentUser);
    
    // Check if users are matched
    $isMatched = \App\Models\UserMatch::areMatched($currentUser->id, $id);
    
    return Inertia::render('Profile/View', [
        'id' => $id,
        'userData' => $user,
        'compatibility' => $compatibilityScore,
        'userTier' => $userTier,
        'isMatched' => $isMatched
    ]);
})->middleware(['auth'])->name('profile.view');

// Debug route to simulate payment completion
Route::get('/debug/simulate-payment/{bookingId}', function ($bookingId) {
    $user = Auth::user();
    
    $booking = \App\Models\TherapistBooking::with(['user', 'therapist'])
        ->where('id', $bookingId)
        ->where('user_id', $user->id)
        ->first();
    
    if (!$booking) {
        return response()->json([
            'error' => 'Booking not found or not owned by current user',
            'booking_id' => $bookingId,
            'user_id' => $user->id
        ]);
    }
    
    try {
        // Simulate payment completion
        $booking->update([
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'payment_reference' => 'DEBUG_' . time()
        ]);
        
        // Send notification
        $user->notify(new \App\Notifications\TherapistBookingPaid($booking));
        
        return response()->json([
            'success' => true,
            'message' => 'Payment simulated and notification sent',
            'booking_id' => $booking->id,
            'booking_status' => $booking->status,
            'payment_status' => $booking->payment_status,
            'therapist_name' => $booking->therapist->name,
            'notification_sent' => true
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Failed to simulate payment',
            'message' => $e->getMessage(),
            'booking_id' => $booking->id
        ]);
    }
})->middleware(['auth'])->name('debug.simulate-payment');

// Subscription routes
Route::middleware(['auth'])->group(function () {
    Route::get('/subscription', [App\Http\Controllers\SubscriptionController::class, 'index'])->name('subscription.index');
    Route::post('/subscription/purchase', [App\Http\Controllers\SubscriptionController::class, 'purchase'])->name('subscription.purchase');
});

// Payment routes
Route::middleware('auth')->group(function () {
    Route::post('/payment/subscription/initialize', [App\Http\Controllers\PaymentController::class, 'initializeSubscription'])->name('payment.subscription.initialize');
    Route::post('/payment/therapist/initialize', [App\Http\Controllers\PaymentController::class, 'initializeTherapistBooking'])->name('payment.therapist.initialize');
    Route::get('/payment/callback', [App\Http\Controllers\PaymentController::class, 'handleCallback'])->name('payment.callback');
});

Route::post('/paystack/webhook', [App\Http\Controllers\PaymentController::class, 'handleWebhook'])->name('paystack.webhook');
Route::post('/monnify/webhook', [App\Http\Controllers\PaymentController::class, 'handleWebhook'])->name('monnify.webhook');

// API route to get fresh CSRF token
Route::get('/api/csrf-token', function () {
    return response()->json([
        'csrf_token' => csrf_token(),
        'timestamp' => now()->toISOString()
    ]);
})->name('api.csrf-token');

// Test Zoho Bookings connection
Route::get('/test-zoho-bookings', function () {
    $zohoService = app(\App\Services\ZohoBookingsService::class);
    
    $status = $zohoService->getStatus();
    $connection = $zohoService->testConnection();
    
    return response()->json([
        'status' => $status,
        'connection_test' => $connection,
        'config' => [
            'enabled' => config('services.zoho_bookings.enabled'),
            'client_id_set' => !empty(config('services.zoho_bookings.client_id')),
            'client_secret_set' => !empty(config('services.zoho_bookings.client_secret')),
            'refresh_token_set' => !empty(config('services.zoho_bookings.refresh_token')),
            'organization_id_set' => !empty(config('services.zoho_bookings.organization_id')),
        ]
    ], 200, [], JSON_PRETTY_PRINT);
})->name('test.zoho.bookings');

require __DIR__.'/auth.php';

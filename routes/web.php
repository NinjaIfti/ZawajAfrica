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
    
    // Photos are already formatted by the MatchingService
    // No additional processing needed for potential matches
    
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

// Test route for debugging
Route::get('/admin/test-verification', function () {
    return Inertia::render('Admin/TestVerification');
})->middleware(['auth'])->name('admin.test-verification');

// Debug route to check user tier
Route::get('/debug/tier', function () {
    $user = Auth::user();
    $tierService = app(\App\Services\UserTierService::class);
    $userTier = $tierService->getUserTier($user);
    
    return response()->json([
        'user_id' => $user->id,
        'subscription_plan' => $user->subscription_plan,
        'subscription_status' => $user->subscription_status,
        'subscription_expires_at' => $user->subscription_expires_at,
        'calculated_tier' => $userTier,
        'tier_limits' => $tierService->getUserLimits($user)
    ]);
})->middleware(['auth'])->name('debug.tier');



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

// Test route for debugging therapist booking
Route::get('/test-therapist-booking', function () {
    try {
        $therapist = \App\Models\Therapist::first();
        if (!$therapist) {
            return response()->json(['error' => 'No therapist found in database']);
        }
        
        return response()->json([
            'therapist_id' => $therapist->id,
            'name' => $therapist->name,
            'hourly_rate' => $therapist->hourly_rate,
            'hourly_rate_type' => gettype($therapist->hourly_rate),
            'all_fields' => $therapist->toArray()
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
    }
})->name('test.therapist.booking');

Route::post('/paystack/webhook', [App\Http\Controllers\PaymentController::class, 'handleWebhook'])->name('paystack.webhook');
Route::post('/monnify/webhook', [App\Http\Controllers\PaymentController::class, 'handleWebhook'])->name('monnify.webhook');

// Test route for payment success modal
Route::get('/test-payment-success', function () {
    return redirect()->route('dashboard')->with([
        'payment_success' => true,
        'payment_type' => 'subscription'
    ]);
})->middleware('auth')->name('test.payment.success');

// Test route specifically for subscription payment success
Route::get('/test-subscription-success', function () {
    return redirect()->route('subscription.index')->with([
        'payment_success' => true,
        'payment_type' => 'subscription'
    ]);
})->middleware('auth')->name('test.subscription.success');

// Test route specifically for therapist booking payment success
Route::get('/test-therapist-success', function () {
    return redirect()->route('therapists.index')->with([
        'payment_success' => true,
        'payment_type' => 'therapist_booking'
    ]);
})->middleware('auth')->name('test.therapist.success');

// Test route for OpenAI integration
Route::get('/test-openai', function () {
    try {
        $openAI = app(\App\Services\OpenAIService::class);
        
        // Check if service is available
        if (!$openAI->isAvailable()) {
            return response()->json([
                'error' => 'OpenAI service not available',
                'config' => [
                    'enabled' => config('services.openai.enabled'),
                    'api_key_set' => !empty(config('services.openai.api_key')),
                    'api_key_length' => strlen(config('services.openai.api_key') ?? ''),
                    'model' => config('services.openai.model'),
                ]
            ]);
        }
        
        // Test simple message
        $messages = [
            ['role' => 'user', 'content' => 'Hello, can you help me?']
        ];
        
        $response = $openAI->chat($messages, auth()->id());
        
        return response()->json([
            'success' => $response['success'],
            'response' => $response,
            'config' => [
                'model' => config('services.openai.model'),
                'api_url' => config('services.openai.api_url'),
            ]
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'config' => [
                'enabled' => config('services.openai.enabled'),
                'api_key_set' => !empty(config('services.openai.api_key')),
                'model' => config('services.openai.model'),
            ]
        ]);
    }
})->middleware('auth')->name('test.openai');

// Test route for Zoho Mail integration (admin only)
Route::get('/test-zoho-mail', function () {
    try {
        $zohoMailService = app(\App\Services\ZohoMailService::class);
        
        // Check configuration status
        $status = $zohoMailService->getStatus();
        
        if (!$status['configured']) {
            return response()->json([
                'status' => 'error',
                'message' => 'Zoho Mail not configured. Please add environment variables.',
                'config_status' => $status,
                'required_env_vars' => [
                    'ZOHO_MAIL_ENABLED=true',
                    'ZOHO_MAIL_USERNAME=support@yourdomain.com',
                    'ZOHO_MAIL_PASSWORD=your_app_password',
                    'ZOHO_MAIL_FROM_ADDRESS=support@yourdomain.com'
                ],
                'recommended_addresses' => $zohoMailService->getRecommendedEmailAddresses()
            ], 400);
        }
        
        // Test email sending
        $testResult = $zohoMailService->testConnection();
        
        return response()->json([
            'status' => $testResult['status'] ? 'success' : 'error',
            'message' => $testResult['message'],
            'config_status' => $status,
            'recommended_addresses' => $zohoMailService->getRecommendedEmailAddresses(),
            'timestamp' => now()->format('Y-m-d H:i:s')
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Error testing Zoho Mail: ' . $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
})->middleware('auth')->name('test.zoho.mail');

// Test route for Zoho Bookings integration (admin only)
Route::get('/test-zoho-bookings', function () {
    try {
        $zohoBookingsService = app(\App\Services\ZohoBookingsService::class);
        
        // Check configuration status
        $status = $zohoBookingsService->getStatus();
        
        if (!$status['configured']) {
            return response()->json([
                'status' => 'error',
                'message' => 'Zoho Bookings not configured. Please add environment variables.',
                'config_status' => $status,
                'required_env_vars' => [
                    'ZOHO_BOOKINGS_ENABLED=true',
                    'ZOHO_BOOKINGS_CLIENT_ID=your_client_id',
                    'ZOHO_BOOKINGS_CLIENT_SECRET=your_client_secret',
                    'ZOHO_BOOKINGS_REFRESH_TOKEN=your_refresh_token',
                    'ZOHO_BOOKINGS_ORGANIZATION_ID=your_org_id',
                    'ZOHO_BOOKINGS_DATA_CENTER=com'
                ]
            ], 400);
        }
        
        // Test connection
        $testResult = $zohoBookingsService->testConnection();
        
        return response()->json([
            'status' => $testResult['success'] ? 'success' : 'error',
            'message' => $testResult['message'],
            'config_status' => $status,
            'services_count' => $testResult['services_count'] ?? 0,
            'timestamp' => now()->format('Y-m-d H:i:s')
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Error testing Zoho Bookings: ' . $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
})->middleware('auth')->name('test.zoho.bookings');

// Matches routes (for authenticated users)
Route::middleware(['auth', 'verified'])->group(function () {
    // Like/Pass functionality (API endpoints)
    Route::post('/api/matches/{user}/like', [MatchController::class, 'like'])->name('matches.like');
    Route::post('/api/matches/{user}/pass', [MatchController::class, 'pass'])->name('matches.pass');
    
    // Search and filter routes
    Route::get('/api/matches/search', [MatchController::class, 'search'])->name('matches.search');
    Route::post('/api/matches/filter', [MatchController::class, 'filter'])->name('matches.filter');
});

// API route to get fresh CSRF token
Route::get('/api/csrf-token', function () {
    return response()->json([
        'csrf_token' => csrf_token(),
        'timestamp' => now()->toISOString()
    ]);
})->name('api.csrf-token');

// Test route for verification notifications (admin only)
Route::get('/test-verification-emails', function () {
    try {
        // Check if user is admin
        $currentUser = auth()->user();
        if (!$currentUser || $currentUser->email !== 'admin@zawagafrica.com') {
            return response()->json([
                'error' => 'Access denied. Admin only.'
            ], 403);
        }

        // Get Zoho Mail service status
        $zohoMailService = app(\App\Services\ZohoMailService::class);
        $mailStatus = $zohoMailService->getStatus();
        
        // Get user verification statistics
        $verificationStats = [
            'total_users' => \App\Models\User::count(),
            'pending_verifications' => \App\Models\Verification::where('status', 'pending')->count(),
            'approved_verifications' => \App\Models\Verification::where('status', 'approved')->count(),
            'rejected_verifications' => \App\Models\Verification::where('status', 'rejected')->count(),
        ];
        
        // Get a test user (first non-admin user)
        $testUser = \App\Models\User::where('email', '!=', 'admin@zawagafrica.com')->first();
        
        $response = [
            'mail_service' => [
                'configured' => $mailStatus['configured'],
                'smtp_host' => $mailStatus['smtp_host'],
                'from_address' => $mailStatus['from_address'],
                'from_name' => $mailStatus['from_name']
            ],
            'verification_stats' => $verificationStats,
            'test_user' => $testUser ? [
                'id' => $testUser->id,
                'name' => $testUser->name,
                'email' => $testUser->email,
                'is_verified' => $testUser->is_verified ?? false,
                'has_verification_record' => $testUser->verification ? true : false,
                'verification_status' => $testUser->verification->status ?? 'none'
            ] : null,
            'notification_classes' => [
                'approval' => 'App\\Notifications\\VerificationApproved',
                'rejection' => 'App\\Notifications\\VerificationRejected'
            ],
            'test_commands' => [
                'Test approval email' => 'php artisan verification:test-email --user-id=1 --type=approved',
                'Test rejection email' => 'php artisan verification:test-email --user-id=1 --type=rejected'
            ]
        ];
        
        return response()->json($response, 200, [], JSON_PRETTY_PRINT);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Test failed: ' . $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
})->middleware('auth')->name('test.verification.emails');

require __DIR__.'/auth.php';

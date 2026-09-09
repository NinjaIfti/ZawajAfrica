<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MatchController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Http\Middleware\TierAccessMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

// Include admin routes
require __DIR__.'/admin.php';

// Redirect root to login page
Route::get('/', function () {
    return redirect()->route('login');
});

// Dashboard with user profile data
Route::get('/dashboard', function () {
    $user = auth()->user();
    
    if ($user->isAdmin()) {
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
    $matchResults = $matchingService->getMatches($user, [], 1000); // Unlimited matches (high limit)
    $potentialMatches = $matchResults['matches']; // Use the formatted matches directly
    
    // Get recent messages for the widget
    $conversations = $user->conversations()->take(5);
    $recentMessages = $conversations->map(function ($conversationUser) use ($user) {
        // Get the latest message between these users
        $latestMessage = \App\Models\Message::where(function ($query) use ($user, $conversationUser) {
            $query->where('sender_id', $user->id)
                  ->where('receiver_id', $conversationUser->id);
        })->orWhere(function ($query) use ($user, $conversationUser) {
            $query->where('sender_id', $conversationUser->id)
                  ->where('receiver_id', $user->id);
        })
        ->latest()
        ->first();
        
        // Count unread messages
        $unreadCount = \App\Models\Message::where('sender_id', $conversationUser->id)
                             ->where('receiver_id', $user->id)
                             ->where('is_read', false)
                             ->count();
        
        // Format profile photo URL
        $profilePhoto = $conversationUser->profile_photo 
            ? asset('storage/' . $conversationUser->profile_photo) 
            : '/images/placeholder.jpg';
        
        return [
            'id' => $conversationUser->id,
            'name' => $conversationUser->name,
            'image' => $profilePhoto, // MessagesWidget expects 'image' not 'profile_photo'
            'message' => $latestMessage ? $latestMessage->content : '',
            'time' => $latestMessage ? $latestMessage->created_at->format('H:i') : '',
            'unread' => $unreadCount > 0,
        ];
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
        'recentMessages' => $recentMessages,
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

    // New Me profile routes
    Route::get('/me/profile', [App\Http\Controllers\Me\ProfileController::class, 'index'])->name('me.profile');
    
    Route::get('/me/photos', [App\Http\Controllers\Me\PhotosController::class, 'index'])->name('me.photos');
    Route::post('/me/photos/upload', [App\Http\Controllers\Me\PhotosController::class, 'upload'])->name('me.photos.upload');
    Route::delete('/me/photos/{id}', [App\Http\Controllers\Me\PhotosController::class, 'delete'])->name('me.photos.delete');
    Route::put('/me/photos/{id}/primary', [App\Http\Controllers\Me\PhotosController::class, 'setPrimary'])->name('me.photos.primary');
    
    // Photo blur routes
    Route::post('/photos/unblur', [App\Http\Controllers\PhotoBlurController::class, 'unblur'])->name('photos.unblur');
    Route::post('/photos/toggle-blur', [App\Http\Controllers\PhotoBlurController::class, 'toggleBlur'])->name('photos.toggle-blur');
    Route::get('/photos/blur-settings', [App\Http\Controllers\PhotoBlurController::class, 'getBlurSettings'])->name('photos.blur-settings');
    Route::post('/photos/can-view', [App\Http\Controllers\PhotoBlurController::class, 'canViewPhotos'])->name('photos.can-view');
    
    Route::get('/me/hobbies', [App\Http\Controllers\Me\HobbiesController::class, 'index'])->name('me.hobbies');
    Route::post('/me/hobbies/update', [App\Http\Controllers\Me\HobbiesController::class, 'update'])->name('me.hobbies.update');
    
    Route::get('/me/personality', [App\Http\Controllers\Me\PersonalityController::class, 'index'])->name('me.personality');
    Route::post('/me/personality/update', [App\Http\Controllers\Me\PersonalityController::class, 'update'])->name('me.personality.update');
    
    Route::get('/me/faqs', [App\Http\Controllers\Me\FAQsController::class, 'index'])->name('me.faqs');
    Route::post('/me/faqs/update', [App\Http\Controllers\Me\FAQsController::class, 'update'])->name('me.faqs.update');
    
    // Matches routes (API only - no index page)
    Route::post('/matches/{user}/like', [App\Http\Controllers\MatchController::class, 'like'])->name('matches.like');
    Route::post('/matches/pass', [App\Http\Controllers\MatchController::class, 'pass'])->name('matches.pass');
    Route::get('/matches/filters', [App\Http\Controllers\MatchController::class, 'getFilters'])->name('matches.filters');
    
    // Messages routes
    Route::get('/messages', [App\Http\Controllers\MessageController::class, 'index'])->name('messages');
    Route::get('/messages/{id}', [App\Http\Controllers\MessageController::class, 'show'])->name('messages.show');
    Route::post('/messages', [App\Http\Controllers\MessageController::class, 'store'])->name('messages.store');
    
    // Add new routes for profile updates
    Route::post('/profile/update', [App\Http\Controllers\Me\ProfileController::class, 'update']);
    Route::post('/profile/photo-update', [App\Http\Controllers\Me\ProfileController::class, 'updatePhoto']);
    
    // Notification routes
    Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'page'])->name('notifications.index');
    Route::get('/notifications/data', [App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.data');
    
    // User activity tracking route
    Route::post('/user/activity', function () {
        if (auth()->check()) {
            $user = auth()->user();
            $user->last_activity_at = now();
            $user->save();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false], 401);
    })->name('user.activity');
    
    // Online status checking route
    Route::post('/api/users/online-status', function (Illuminate\Http\Request $request) {
        if (!auth()->check()) {
            return response()->json(['success' => false], 401);
        }
        
        $userIds = $request->input('user_ids', []);
        if (empty($userIds) || !is_array($userIds)) {
            return response()->json(['success' => false, 'error' => 'Invalid user IDs'], 400);
        }
        
        // Limit to 100 users max to prevent abuse
        $userIds = array_slice($userIds, 0, 100);
        
        $users = \App\Models\User::whereIn('id', $userIds)
            ->select('id', 'last_activity_at')
            ->get();
        
        $onlineStatus = [];
        foreach ($users as $user) {
            $onlineStatus[$user->id] = $user->isOnline();
        }
        
        return response()->json([
            'success' => true,
            'online_status' => $onlineStatus
        ]);
    })->name('api.users.online-status');
    
    // User subscription refresh route
    Route::get('/api/user/subscription', function () {
        $user = Auth::user();
        return response()->json([
            'subscription_plan' => $user->subscription_plan,
            'subscription_status' => $user->subscription_status,
            'subscription_expires_at' => $user->subscription_expires_at,
        ]);
    })->name('user.subscription.status');
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
    Route::get('/verification/{verification}/{side}', [App\Http\Controllers\VerificationController::class, 'document'])->where('side', 'front|back')->name('verification.document');
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

// Subscription routes
Route::middleware(['auth'])->group(function () {
    Route::get('/subscription', [App\Http\Controllers\SubscriptionController::class, 'index'])->name('subscription.index');
    Route::get('/subscription/manual-payment', [App\Http\Controllers\SubscriptionController::class, 'showManualPayment'])->name('subscription.manual-payment');
    Route::post('/subscription/purchase', [App\Http\Controllers\SubscriptionController::class, 'purchase'])->name('subscription.purchase');
});

// Payment callback route - outside auth middleware to prevent issues
Route::get('/payment/callback', [App\Http\Controllers\PaymentController::class, 'handleCallback'])->name('payment.callback');

// Payment routes
Route::middleware('auth')->group(function () {
    Route::post('/payment/subscription/initialize', [App\Http\Controllers\PaymentController::class, 'initializeSubscription'])->name('payment.subscription.initialize');
    });


// API route to get fresh CSRF token
Route::get('/api/csrf-token', function () {
    return response()->json([
        'csrf_token' => csrf_token(),
        'timestamp' => now()->toISOString()
    ]);
})->name('api.csrf-token');

// API route to get current user data for payment verification
Route::middleware('auth')->get('/api/user', function () {
    $user = Auth::user();
    return response()->json([
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'subscription_status' => $user->subscription_status,
        'subscription_plan' => $user->subscription_plan,
        'subscription_expires_at' => $user->subscription_expires_at
    ]);
})->name('api.user');

// Chat Credits API
Route::middleware('auth')->get('/api/chat-credits', function (Request $request) {
    $user = Auth::user();
    
    $adUnlockService = app(\App\Services\AdUnlockChatService::class);
    $status = $adUnlockService->getChatUnlockStatus($user);
    
    return response()->json($status);
})->name('api.chat-credits');

// Message Badge API
Route::middleware('auth')->get('/api/message-badge', function (Request $request) {
    $user = Auth::user();
    
    $badgeService = app(\App\Services\MessageBadgeService::class);
    $badgeData = $badgeService->getBadgeData($user);
    
    return response()->json($badgeData);
})->name('api.message-badge');

// Recent Messages API for notifications
Route::middleware('auth')->get('/api/recent-messages', function (Request $request) {
    $user = Auth::user();
    
    $badgeService = app(\App\Services\MessageBadgeService::class);
    $recentMessages = $badgeService->getRecentUnreadMessages($user, 10);
    
    return response()->json([
        'messages' => $recentMessages,
        'total_unread' => $badgeService->getUnreadCount($user)
    ]);
})->name('api.recent-messages');

Route::get('/mobile-login', function () {
    return inertia('Auth/MobileLogin', [
        'canResetPassword' => Route::has('password.request'),
        'status' => session('status'),
    ]);
});

// User messaging status API route (outside of adsterra prefix)
Route::middleware('auth')->get('/api/user/messaging-status', function (Request $request) {
    $user = Auth::user();
    
    if (!$user) {
        return response()->json(['error' => 'Authentication required'], 401);
    }
    
    $adUnlockService = app(\App\Services\AdUnlockChatService::class);
    $status = $adUnlockService->getChatUnlockStatus($user);
    
    return response()->json([
        'success' => true,
        'has_credits' => $status['has_credits'] ?? false,
        'remaining_credits' => $status['remaining_credits'] ?? 0,
        'can_watch_ads' => $status['available'] ?? false,
        'expires_at' => $status['credits_expire_at'] ?? null
    ]);
})->name('api.user.messaging-status');

// Email Template Routes for Zoho Campaigns

Route::get('/email-templates/welcome-email', [App\Http\Controllers\EmailTemplateController::class, 'welcomeEmail']);
Route::get('/email-templates/newsletter', [App\Http\Controllers\EmailTemplateController::class, 'newsletter']);
Route::get('/email-templates/{template}', [App\Http\Controllers\EmailTemplateController::class, 'template']);

require __DIR__.'/auth.php';

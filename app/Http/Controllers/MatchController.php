<?php

namespace App\Http\Controllers;

use App\Services\MatchingService;
use App\Services\UserTierService;
use App\Models\User;
use App\Models\UserLike;
use App\Models\UserMatch;
use App\Notifications\NewMatchFound;
use App\Notifications\NewLikeReceived;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Facades\Cache;

class MatchController extends Controller
{
    protected MatchingService $matchingService;
    protected UserTierService $tierService;

    public function __construct(MatchingService $matchingService, UserTierService $tierService)
    {
        $this->matchingService = $matchingService;
        $this->tierService = $tierService;
    }

    /**
     * Send a like to another user
     */
    public function like(Request $request, $user)
    {
        $targetUserId = $user; // Get user ID from URL parameter

        $currentUser = Auth::user();

        // Rate limiting - max 5 likes per minute
        $rateLimitKey = "user_likes_rate_limit:{$currentUser->id}";
        $likesInLastMinute = Cache::get($rateLimitKey, 0);
        
        if ($likesInLastMinute >= 5) {
            \Log::warning("Like rate limit exceeded", [
                'user_id' => $currentUser->id,
                'tier' => $this->tierService->getUserTier($currentUser),
                'attempts_in_minute' => $likesInLastMinute
            ]);
            
            return response()->json([
                'error' => 'Too many likes sent. Please wait a moment before liking again.',
                'rate_limited' => true
            ], 429);
        }

        // Check if user can view profiles first (without recording activity yet)
        $canView = $this->tierService->canViewProfile($currentUser);
        if (!$canView['allowed']) {
            \Log::info("Like attempt blocked by view limit", [
                'user_id' => $currentUser->id,
                'tier' => $this->tierService->getUserTier($currentUser),
                'daily_views_used' => $canView['used'],
                'daily_limit' => $canView['limit']
            ]);
            
            return response()->json([
                'error' => 'Daily profile view limit reached',
                'upgrade_required' => true
            ], 429);
        }

        // Use database transaction to prevent race conditions
        $matchCreated = false;
        $targetUser = null;
        $error = null;
        
        try {
            DB::transaction(function () use ($currentUser, $targetUserId, &$matchCreated, &$targetUser, &$error) {
                // Get target user within transaction - quick validation
                $targetUser = \App\Models\User::find($targetUserId);
                if (!$targetUser) {
                    $error = 'Target user not found';
                    return;
                }
                
                // Check if already liked or matched within the transaction - quick checks
                if (UserLike::where('user_id', $currentUser->id)->where('liked_user_id', $targetUserId)->exists()) {
                    $error = 'You have already liked this user';
                    return;
                }
                
                if (UserMatch::areMatched($currentUser->id, $targetUserId)) {
                    $error = 'You are already matched with this user';
                    return;
                }

                // Create the like - fast operation
                UserLike::create([
                    'user_id' => $currentUser->id,
                    'liked_user_id' => $targetUserId,
                    'status' => 'pending',
                    'liked_at' => now()
                ]);

                // Check for mutual like within transaction - optimized query
                $isMutual = UserLike::isMutualLike($currentUser->id, $targetUserId);

                if ($isMutual) {
                    // Create a match - fast operation
                    UserMatch::createMatch($currentUser->id, $targetUserId);
                    
                    // Mark likes as matched - fast operation
                    UserLike::markAsMatched($currentUser->id, $targetUserId);

                    $matchCreated = true;
                    
                    // Log successful match creation (minimal logging for speed)
                    \Log::info("Match created", [
                        'user_1' => $currentUser->id,
                        'user_2' => $targetUserId
                    ]);
                }
            });
        } catch (\Exception $e) {
            \Log::error("Like operation failed", [
                'user_id' => $currentUser->id,
                'target_user_id' => $targetUserId,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'error' => 'Like operation failed. Please try again.',
                'debug' => app()->environment('local') ? $e->getMessage() : null
            ], 500);
        }

        // Handle errors from transaction
        if ($error) {
            $statusCode = 400;
            $response = ['error' => $error];
            
            if ($error === 'You have already liked this user') {
                $response['already_liked'] = true;
            } elseif ($error === 'You are already matched with this user') {
                $response['already_matched'] = true;
            }
            
            return response()->json($response, $statusCode);
        }

        // Increment rate limiting counter after successful operation
        Cache::put($rateLimitKey, $likesInLastMinute + 1, now()->addMinute());

        // IMMEDIATELY return success response to frontend
        $response = [
            'success' => true,
            'message' => $matchCreated ? 'It\'s a match!' : 'Like sent successfully',
            'match_created' => $matchCreated,
            'can_message' => $this->tierService->canSendMessage($currentUser)['allowed']
        ];

        // Queue notifications asynchronously AFTER responding to frontend
        if ($matchCreated) {
            // Send instant database notifications and queue delayed emails
            dispatch(function () use ($currentUser, $targetUser) {
                try {
                    // Send instant database notifications
                    $matchNotification1 = new NewMatchFound($targetUser);
                    $matchNotification2 = new NewMatchFound($currentUser);
                    
                    $currentUser->notify($matchNotification1);
                    $targetUser->notify($matchNotification2);
                    
                    // Send delayed email notifications (30 seconds later) - without queue dependency
                    self::sendMatchEmailAfterDelay($currentUser, $targetUser);
                    self::sendMatchEmailAfterDelay($targetUser, $currentUser);
                    
                    \Log::info('Match notifications sent (instant DB) and emails queued (delayed)', [
                        'user_1' => $currentUser->id,
                        'user_2' => $targetUser->id
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Failed to send match notifications', [
                        'user_1' => $currentUser->id,
                        'user_2' => $targetUser->id,
                        'error' => $e->getMessage()
                    ]);
                }
            })->afterResponse();
        } else {
            // Send instant database notification and queue delayed email
            dispatch(function () use ($currentUser, $targetUser) {
                try {
                    $likeNotification = new NewLikeReceived($currentUser, $targetUser);
                    
                    // Send instant database notification
                    $targetUser->notify($likeNotification);
                    
                    // Send delayed email notification (30 seconds later) - without queue dependency
                    self::sendLikeEmailAfterDelay($currentUser, $targetUser);
                    
                    \Log::info('Like notification sent (instant DB) and email queued (delayed)', [
                        'liker_id' => $currentUser->id,
                        'receiver_id' => $targetUser->id
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Failed to send like notification', [
                        'liker_id' => $currentUser->id,
                        'receiver_id' => $targetUser->id,
                        'error' => $e->getMessage()
                    ]);
                }
            })->afterResponse();
        }

        return response()->json($response);
    }

    /**
     * Send match email after delay (without queue dependency)
     */
    private static function sendMatchEmailAfterDelay(User $user, User $matchedUser): void
    {
        // Schedule email to be sent in 30 seconds using cache-based scheduling
        $emailData = [
            'type' => 'match',
            'user_id' => $user->id,
            'matched_user_id' => $matchedUser->id,
            'scheduled_at' => now()->addSeconds(30)->timestamp
        ];
        
        $cacheKey = "scheduled_email_match_{$user->id}_{$matchedUser->id}_" . time();
        cache()->put($cacheKey, $emailData, 300); // 5 minutes expiry
        
        // Immediately try to process if possible
        self::processScheduledEmails();
    }

    /**
     * Send like email after delay (without queue dependency)  
     */
    private static function sendLikeEmailAfterDelay(User $liker, User $receiver): void
    {
        // Schedule email to be sent in 30 seconds using cache-based scheduling
        $emailData = [
            'type' => 'like',
            'liker_id' => $liker->id,
            'receiver_id' => $receiver->id,
            'scheduled_at' => now()->addSeconds(30)->timestamp
        ];
        
        $cacheKey = "scheduled_email_like_{$liker->id}_{$receiver->id}_" . time();
        cache()->put($cacheKey, $emailData, 300); // 5 minutes expiry
        
        // Immediately try to process if possible
        self::processScheduledEmails();
    }

    /**
     * Process scheduled emails that are ready to be sent
     */
    private static function processScheduledEmails(): void
    {
        // This method will be called by a cron job or manual trigger
        // Implement proper email processing logic here
        self::sendAllScheduledEmails();
    }

    /**
     * Send all scheduled emails that are ready
     */
    private static function sendAllScheduledEmails(): void
    {
        try {
            \Log::info('Scheduled email processing started');
            
            $zohoMailService = app(\App\Services\ZohoMailService::class);
            $zohoMailService->configureMailer();

            // Check for users who liked/matched in last 30-60 seconds for delayed emails
            $recentLikes = \App\Models\UserLike::with(['user', 'likedUser'])
                ->whereBetween('created_at', [now()->subSeconds(60), now()->subSeconds(30)])
                ->get();

            foreach ($recentLikes as $like) {
                // Check if this became a match
                $isMatch = \App\Models\UserLike::where('user_id', $like->liked_user_id)
                    ->where('liked_user_id', $like->user_id)
                    ->exists();

                if ($isMatch) {
                    self::sendMatchEmailNow($like->user, $like->likedUser);
                    self::sendMatchEmailNow($like->likedUser, $like->user);
                    \Log::info('Match email sent', ['user1' => $like->user_id, 'user2' => $like->liked_user_id]);
                } else {
                    self::sendLikeEmailNow($like->user, $like->likedUser);
                    \Log::info('Like email sent', ['liker' => $like->user_id, 'receiver' => $like->liked_user_id]);
                }
            }
            
        } catch (\Exception $e) {
            \Log::error('Failed to process scheduled emails', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Manual trigger for sending scheduled emails (can be called via cron)
     */
    public function sendScheduledEmails(): void
    {
        try {
            $zohoMailService = app(\App\Services\ZohoMailService::class);
            $zohoMailService->configureMailer();

            // For now, we'll use a simple approach - check for users who liked/matched in last 30-60 seconds
            $recentLikes = \App\Models\UserLike::with(['user', 'likedUser'])
                ->whereBetween('created_at', [now()->subSeconds(60), now()->subSeconds(30)])
                ->get();

            foreach ($recentLikes as $like) {
                // Check if this became a match
                $isMatch = \App\Models\UserLike::where('user_id', $like->liked_user_id)
                    ->where('liked_user_id', $like->user_id)
                    ->exists();

                if ($isMatch) {
                    self::sendMatchEmailNow($like->user, $like->likedUser);
                    self::sendMatchEmailNow($like->likedUser, $like->user);
                    \Log::info('Match email sent', ['user1' => $like->user_id, 'user2' => $like->liked_user_id]);
                } else {
                    self::sendLikeEmailNow($like->user, $like->likedUser);
                    \Log::info('Like email sent', ['liker' => $like->user_id, 'receiver' => $like->liked_user_id]);
                }
            }

        } catch (\Exception $e) {
            \Log::error('Failed to send scheduled emails', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send match email immediately
     */
    private static function sendMatchEmailNow(User $user, User $matchedUser): void
    {
        try {
            $zohoMailService = app(\App\Services\ZohoMailService::class);
            $zohoMailService->configureMailer();

            $subject = '🌟 It\'s a Match! You and ' . $matchedUser->name . ' liked each other!';
            
            $content = "Hi " . $user->name . ",\n\n" .
                      "🎉 Congratulations! You have a new match!\n\n" .
                      "You and " . $matchedUser->name . " both liked each other!\n\n" .
                      "This means you can now start messaging each other and get to know one another better.\n\n" .
                      "Start messaging: " . url('/messages') . "\n\n" .
                      "Next Steps:\n" .
                      "• Send a thoughtful first message\n" .
                      "• Be genuine and respectful in your conversations\n" .
                      "• Take your time to get to know each other\n\n" .
                      "We're excited to see where this connection leads!\n\n" .
                      "Best regards,\nZawajAfrica Team";

            \Mail::raw($content, function ($message) use ($user, $subject) {
                $message->to($user->email, $user->name)
                        ->subject($subject);
            });

            \Log::info('Match email sent successfully', [
                'user_id' => $user->id,
                'matched_user_id' => $matchedUser->id
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to send match email', [
                'user_id' => $user->id,
                'matched_user_id' => $matchedUser->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send like email immediately
     */
    private static function sendLikeEmailNow(User $liker, User $receiver): void
    {
        try {
            $zohoMailService = app(\App\Services\ZohoMailService::class);
            $zohoMailService->configureMailer();

            // Determine if we can reveal liker's name (based on receiver's tier)
            $tierService = app(\App\Services\UserTierService::class);
            $receiverTier = $tierService->getUserTier($receiver);
            
            $canReveal = in_array($receiverTier, [
                \App\Services\UserTierService::TIER_BASIC,
                \App\Services\UserTierService::TIER_GOLD, 
                \App\Services\UserTierService::TIER_PLATINUM
            ]);

            if ($canReveal) {
                // For paid users - show actual liker's name
                $subject = '💕 ' . $liker->name . ' likes you on ZawajAfrica!';
                $content = "Hi " . $receiver->name . ",\n\n" .
                          $liker->name . " has liked your profile!\n\n" .
                          "Check out their profile and maybe like them back for a potential match!\n\n" .
                          "View their profile: " . url('/profile/view/' . $liker->id) . "\n\n" .
                          "Don't miss out on potential connections!\n\n" .
                          "Best regards,\nZawajAfrica Team";
            } else {
                // For free users - anonymous message with upgrade prompt
                $subject = '💕 Someone likes you on ZawajAfrica!';
                $content = "Hi " . $receiver->name . ",\n\n" .
                          "Someone has liked your profile!\n\n" .
                          "Upgrade to a paid plan to see who liked you and unlock more features!\n\n" .
                          "Upgrade now: " . url('/subscription') . "\n\n" .
                          "Don't miss out on potential connections!\n\n" .
                          "Best regards,\nZawajAfrica Team";
            }

            \Mail::raw($content, function ($message) use ($receiver, $subject) {
                $message->to($receiver->email, $receiver->name)
                        ->subject($subject);
            });

            \Log::info('Like email sent successfully', [
                'liker_id' => $liker->id,
                'receiver_id' => $receiver->id,
                'revealed' => $canReveal
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to send like email', [
                'liker_id' => $liker->id,
                'receiver_id' => $receiver->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Pass on a user (skip)
     */
    public function pass(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $user = Auth::user();

        // Record the profile view if allowed
        $viewStatus = $this->tierService->recordProfileView($user);
        if (!$viewStatus['allowed']) {
            return response()->json([
                'error' => 'Daily profile view limit reached',
                'upgrade_required' => true
            ], 429);
        }

        // Here you would typically create a "pass" record in the database
        // TODO: Implement user pass tracking to avoid showing same profiles repeatedly

        return response()->json([
            'success' => true,
            'message' => 'Profile skipped',
            'remaining_views' => $viewStatus['remaining']
        ]);
    }

    /**
     * Search users by name
     */
    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:1|max:50',
            'filters' => 'nullable|array'
        ]);

        $user = Auth::user();
        $searchTerm = $request->q;
        $filters = $request->filters ?? [];

        try {
            $results = $this->matchingService->searchByName($user, $searchTerm, $filters);
            
            return response()->json([
                'success' => true,
                'data' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Search failed. Please try again.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Apply filters to get matches
     */
    public function filter(Request $request)
    {
        $request->validate([
            'filters' => 'required|array'
        ]);

        $user = Auth::user();
        $filters = $request->filters;

        try {
            $results = $this->matchingService->getMatches($user, $filters);
            
            return response()->json([
                'success' => true,
                'data' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Filtering failed. Please try again.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get advanced search filters based on user tier
     */
    public function getFilters()
    {
        $user = Auth::user();
        $userTier = $this->tierService->getUserTier($user);
        
        return response()->json([
            'filters' => $this->getAvailableFilters($userTier),
            'user_tier' => $userTier
        ]);
    }

    /**
     * Get available filters based on user tier
     */
    private function getAvailableFilters(string $userTier): array
    {
        $filters = [
            'basic' => [
                'age_range' => [
                    'type' => 'range',
                    'label' => 'Age Range',
                    'min' => 18,
                    'max' => 80
                ],
                'location' => [
                    'type' => 'text',
                    'label' => 'Location (City/State)'
                ],
                'marital_status' => [
                    'type' => 'select',
                    'label' => 'Marital Status',
                    'options' => ['single', 'divorced', 'widowed']
                ],
                'religion' => [
                    'type' => 'select',
                    'label' => 'Religion',
                    'options' => ['islam', 'christianity', 'other']
                ]
            ]
        ];

        // Add Gold tier filters
        if (in_array($userTier, [UserTierService::TIER_GOLD, UserTierService::TIER_PLATINUM])) {
            $filters['gold'] = [
                'education_level' => [
                    'type' => 'select',
                    'label' => 'Education Level',
                    'options' => ['high_school', 'diploma', 'bachelor', 'master', 'phd']
                ],
                'occupation' => [
                    'type' => 'text',
                    'label' => 'Occupation'
                ],
                'income_range' => [
                    'type' => 'select',
                    'label' => 'Income Range',
                    'options' => ['under_50k', '50k_100k', '100k_200k', 'over_200k']
                ]
            ];
        }

        // Add Platinum tier filters
        if ($userTier === UserTierService::TIER_PLATINUM) {
            $filters['platinum'] = [
                'height_range' => [
                    'type' => 'range',
                    'label' => 'Height Range (cm)',
                    'min' => 140,
                    'max' => 220
                ],
                'ethnicity' => [
                    'type' => 'select',
                    'label' => 'Ethnicity',
                    'options' => ['african', 'arab', 'asian', 'european', 'mixed', 'other']
                ],
                'smoking' => [
                    'type' => 'select',
                    'label' => 'Smoking',
                    'options' => ['never', 'occasionally', 'regularly']
                ],
                'drinking' => [
                    'type' => 'select',
                    'label' => 'Drinking',
                    'options' => ['never', 'occasionally', 'socially']
                ],
                'elite_only' => [
                    'type' => 'checkbox',
                    'label' => 'Platinum Members Only'
                ]
            ];
        }

        return $filters;
    }

    /**
     * Get AI-powered match suggestions for premium users
     */
    public function getAISuggestions(Request $request)
    {
        $user = Auth::user();
        $userTier = $this->tierService->getUserTier($user);

        // Only available for Gold and Platinum users
        if (!in_array($userTier, [UserTierService::TIER_GOLD, UserTierService::TIER_PLATINUM])) {
            return response()->json([
                'error' => 'AI suggestions are available for Gold and Platinum members only',
                'upgrade_required' => true
            ], 403);
        }

        // Check daily limits
        $canView = $this->tierService->canViewProfile($user);
        if (!$canView['allowed']) {
            return response()->json([
                'error' => 'Daily profile view limit reached',
                'upgrade_required' => true
            ], 429);
        }

        // Get AI-enhanced matches
        $aiMatches = $this->matchingService->getMatches($user, [], 1000); // Unlimited matches

        return response()->json([
            'suggestions' => $aiMatches['matches'],
            'ai_enhanced' => true,
            'tier' => $userTier
        ]);
    }
}

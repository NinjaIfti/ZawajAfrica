<?php

namespace App\Http\Controllers;

use App\Services\MatchingService;
use App\Services\UserTierService;
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
                // Lock the user records to prevent race conditions first
                DB::table('users')->where('id', $currentUser->id)->lockForUpdate()->first();
                DB::table('users')->where('id', $targetUserId)->lockForUpdate()->first();
                
                // Get target user within transaction
                $targetUser = \App\Models\User::find($targetUserId);
                if (!$targetUser) {
                    $error = 'Target user not found';
                    return;
                }
                
                // Check if already liked or matched within the transaction
                if (UserLike::where('user_id', $currentUser->id)->where('liked_user_id', $targetUserId)->exists()) {
                    $error = 'You have already liked this user';
                    return;
                }
                
                if (UserMatch::areMatched($currentUser->id, $targetUserId)) {
                    $error = 'You are already matched with this user';
                    return;
                }

                // Create the like
                UserLike::create([
                    'user_id' => $currentUser->id,
                    'liked_user_id' => $targetUserId,
                    'status' => 'pending',
                    'liked_at' => now()
                ]);

                // Check for mutual like within transaction
                $isMutual = UserLike::isMutualLike($currentUser->id, $targetUserId);

                if ($isMutual) {
                    // Create a match
                    UserMatch::createMatch($currentUser->id, $targetUserId);
                    
                    // Mark likes as matched
                    UserLike::markAsMatched($currentUser->id, $targetUserId);

                    $matchCreated = true;
                    
                    // Log successful match creation
                    \Log::info("Match created", [
                        'user_1' => $currentUser->id,
                        'user_2' => $targetUserId,
                        'user_1_tier' => $this->tierService->getUserTier($currentUser),
                        'user_2_tier' => $this->tierService->getUserTier($targetUser)
                    ]);
                } else {
                    // Log like sent
                    \Log::info("Like sent", [
                        'sender_id' => $currentUser->id,
                        'receiver_id' => $targetUserId,
                        'sender_tier' => $this->tierService->getUserTier($currentUser)
                    ]);
                }
            });
        } catch (\Exception $e) {
            \Log::error("Like operation failed", [
                'user_id' => $currentUser->id,
                'target_user_id' => $targetUserId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
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

        // Send notifications outside transaction
        if ($matchCreated) {
            // Send match notifications to both users
            try {
                // Send notification to first user (current user)
                $currentUser->notify(new NewMatchFound($targetUser));
                \Log::info('Match notification sent to first user', [
                    'user_id' => $currentUser->id,
                    'user_name' => $currentUser->name,
                    'user_email' => $currentUser->email,
                    'match_with' => $targetUser->name
                ]);
                
                // Send notification to second user (target user)
                $targetUser->notify(new NewMatchFound($currentUser));
                \Log::info('Match notification sent to second user', [
                    'user_id' => $targetUser->id,
                    'user_name' => $targetUser->name,
                    'user_email' => $targetUser->email,
                    'match_with' => $currentUser->name
                ]);
                
                \Log::info('Both match notifications sent successfully', [
                    'user_1' => $currentUser->id,
                    'user_2' => $targetUserId
                ]);
            } catch (\Exception $e) {
                \Log::error('Failed to send match notifications', [
                    'user_1' => $currentUser->id,
                    'user_2' => $targetUserId,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        } else {
            // Send like notification (will auto-determine if name should be revealed based on receiver's tier)
            try {
                $targetUser->notify(new NewLikeReceived($currentUser, $targetUser));
                \Log::info('Like notification sent successfully', [
                    'liker_id' => $currentUser->id,
                    'liker_name' => $currentUser->name,
                    'receiver_id' => $targetUserId,
                    'receiver_name' => $targetUser->name,
                    'receiver_email' => $targetUser->email,
                    'receiver_tier' => $this->tierService->getUserTier($targetUser)
                ]);
            } catch (\Exception $e) {
                \Log::error('Failed to send like notification', [
                    'liker_id' => $currentUser->id,
                    'receiver_id' => $targetUserId,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                // Don't throw here - we want the like to still be processed even if notification fails
            }
        }

        // Increment rate limiting counter after successful operation
        Cache::put($rateLimitKey, $likesInLastMinute + 1, now()->addMinute());

        return response()->json([
            'success' => true,
            'message' => $matchCreated ? 'It\'s a match!' : 'Like sent successfully',
            'match_created' => $matchCreated,
            'can_message' => $this->tierService->canSendMessage($currentUser)['allowed']
        ]);
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
        $aiMatches = $this->matchingService->getMatches($user, [], 10);

        return response()->json([
            'suggestions' => $aiMatches['matches'],
            'ai_enhanced' => true,
            'tier' => $userTier
        ]);
    }
}

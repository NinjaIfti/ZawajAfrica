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
    public function like(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $user = Auth::user();
        $targetUserId = $request->user_id;

        // Check if user can view profiles
        $canView = $this->tierService->canViewProfile($user);
        if (!$canView['allowed']) {
            return response()->json([
                'error' => 'Daily profile view limit reached',
                'upgrade_required' => true
            ], 429);
        }

        // Check if already liked or matched
        if (UserLike::where('user_id', $user->id)->where('liked_user_id', $targetUserId)->exists()) {
            return response()->json([
                'error' => 'You have already liked this user',
                'already_liked' => true
            ], 400);
        }

        if (UserMatch::areMatched($user->id, $targetUserId)) {
            return response()->json([
                'error' => 'You are already matched with this user',
                'already_matched' => true
            ], 400);
        }

        // Use database transaction to prevent race conditions
        $matchCreated = false;
        $targetUser = \App\Models\User::find($targetUserId);
        
        DB::transaction(function () use ($user, $targetUserId, $targetUser, &$matchCreated) {
            // Record the profile view activity at the start of transaction
            $this->tierService->recordActivity($user, 'profile_views');
            
            // Lock the user records to prevent race conditions
            DB::table('users')->where('id', $user->id)->lockForUpdate()->first();
            DB::table('users')->where('id', $targetUserId)->lockForUpdate()->first();
            
            // Check if already liked or matched within the transaction
            if (UserLike::where('user_id', $user->id)->where('liked_user_id', $targetUserId)->exists()) {
                throw new \Exception('You have already liked this user');
            }
            
            if (UserMatch::areMatched($user->id, $targetUserId)) {
                throw new \Exception('You are already matched with this user');
            }

            // Create the like
            UserLike::create([
                'user_id' => $user->id,
                'liked_user_id' => $targetUserId,
                'status' => 'pending',
                'liked_at' => now()
            ]);

            // Check for mutual like within transaction
            $isMutual = UserLike::isMutualLike($user->id, $targetUserId);

            if ($isMutual) {
                // Create a match
                UserMatch::createMatch($user->id, $targetUserId);
                
                // Mark likes as matched
                UserLike::markAsMatched($user->id, $targetUserId);

                $matchCreated = true;
            }
        });

        // Send notifications outside transaction
        $senderUserTier = $this->tierService->getUserTier($user);
        $canRevealLiker = in_array($senderUserTier, [UserTierService::TIER_BASIC, UserTierService::TIER_GOLD, UserTierService::TIER_PLATINUM]);
        
        if ($matchCreated) {
            // Send match notifications to both users
            $user->notify(new NewMatchFound($targetUser));
            $targetUser->notify(new NewMatchFound($user));
        } else {
            $targetUser->notify(new NewLikeReceived($user, $canRevealLiker));
        }

        return response()->json([
            'success' => true,
            'message' => $matchCreated ? 'It\'s a match!' : 'Like sent successfully',
            'match_created' => $matchCreated,
            'can_message' => $this->tierService->canSendMessage($user)['allowed']
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

        // Check if user can view profiles
        $canView = $this->tierService->canViewProfile($user);
        if (!$canView['allowed']) {
            return response()->json([
                'error' => 'Daily profile view limit reached',
                'upgrade_required' => true
            ], 429);
        }

        // Record the profile view activity
        $this->tierService->recordActivity($user, 'profile_views');

        // Here you would typically create a "pass" record in the database

        return response()->json([
            'success' => true,
            'message' => 'Profile skipped'
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

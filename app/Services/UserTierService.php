<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UserTierService
{
    // Tier definitions
    public const TIER_FREE = 'free';
    public const TIER_BASIC = 'basic';
    public const TIER_GOLD = 'gold';
    public const TIER_PLATINUM = 'platinum';

    // Daily limits by tier
    private array $tierLimits = [
        self::TIER_FREE => [
            'profile_views' => 50,
            'messages' => 0, // Cannot initiate messages
            'contact_details' => false,
            'ads_frequency' => 10, // Show ads every 10 profiles
            'therapy_sessions' => true,
            'unlimited_search' => false,
            'elite_access' => false,
        ],
        self::TIER_BASIC => [
            'profile_views' => 120,
            'messages' => 30,
            'contact_details' => true,
            'ads_frequency' => 0, // No ads
            'therapy_sessions' => true,
            'unlimited_search' => false,
            'elite_access' => false,
        ],
        self::TIER_GOLD => [
            'profile_views' => -1, // Unlimited
            'messages' => 100,
            'contact_details' => true,
            'ads_frequency' => 0, // No ads
            'therapy_sessions' => true,
            'unlimited_search' => true,
            'elite_access' => false,
        ],
        self::TIER_PLATINUM => [
            'profile_views' => -1, // Unlimited
            'messages' => -1, // Unlimited
            'contact_details' => true,
            'ads_frequency' => 0, // No ads
            'therapy_sessions' => true,
            'unlimited_search' => true,
            'elite_access' => true,
            'custom_filters' => true,
            'priority_support' => true,
        ],
    ];

    /**
     * Get user's current tier
     */
    public function getUserTier(User $user): string
    {
        if (!$user->subscription_plan || $user->subscription_status !== 'active') {
            return self::TIER_FREE;
        }

        // Check if subscription has expired
        if ($user->subscription_expires_at && $user->subscription_expires_at->isPast()) {
            // Update status to expired in database
            $user->update(['subscription_status' => 'expired']);
            return self::TIER_FREE;
        }

        return strtolower($user->subscription_plan);
    }

    /**
     * Get tier limits for a specific tier
     */
    public function getTierLimits(string $tier): array
    {
        return $this->tierLimits[$tier] ?? $this->tierLimits[self::TIER_FREE];
    }

    /**
     * Get user's tier limits
     */
    public function getUserLimits(User $user): array
    {
        $tier = $this->getUserTier($user);
        return $this->getTierLimits($tier);
    }

    /**
     * Check if user can view profiles
     */
    public function canViewProfile(User $user): array
    {
        $limits = $this->getUserLimits($user);
        
        if ($limits['profile_views'] === -1) {
            return ['allowed' => true, 'remaining' => -1];
        }

        $todayViews = $this->getTodayCount($user, 'profile_views');
        $remaining = max(0, $limits['profile_views'] - $todayViews);

        return [
            'allowed' => $remaining > 0,
            'remaining' => $remaining,
            'limit' => $limits['profile_views'],
            'used' => $todayViews
        ];
    }

    /**
     * Check if user can send messages
     */
    public function canSendMessage(User $user): array
    {
        if ($this->getUserTier($user) === self::TIER_FREE) {
            return [
                'allowed' => false,
                'reason' => 'free_tier_restriction',
                'message' => 'Free users cannot send messages. Upgrade to Basic to start messaging!'
            ];
        }

        $limits = $this->getUserLimits($user);
        if ($limits['messages'] === -1) {
            return ['allowed' => true, 'remaining' => -1];
        }

        $todayMessages = $this->getTodayCount($user, 'messages_sent');
        $remaining = max(0, $limits['messages'] - $todayMessages);

        return [
            'allowed' => $remaining > 0,
            'remaining' => $remaining,
            'limit' => $limits['messages'],
            'used' => $todayMessages
        ];
    }

    /**
     * Check if user can access contact details
     */
    public function canAccessContactDetails(User $user): bool
    {
        $limits = $this->getUserLimits($user);
        return $limits['contact_details'] ?? false;
    }

    /**
     * Check if user should see ads
     */
    public function shouldShowAds(User $user, int $currentViewCount = 0): bool
    {
        $limits = $this->getUserLimits($user);
        $adsFrequency = $limits['ads_frequency'] ?? 0;
        
        return $adsFrequency > 0 && ($currentViewCount % $adsFrequency) === 0;
    }

    /**
     * Check if user has elite access
     */
    public function hasEliteAccess(User $user): bool
    {
        $limits = $this->getUserLimits($user);
        return $limits['elite_access'] ?? false;
    }

    /**
     * Record user activity for daily tracking
     */
    public function recordActivity(User $user, string $activity): void
    {
        $today = Carbon::today()->format('Y-m-d');
        $cacheKey = "user_activity:{$user->id}:{$activity}:{$today}";
        
        $currentCount = Cache::get($cacheKey, 0);
        Cache::put($cacheKey, $currentCount + 1, Carbon::tomorrow());

        // Also store in database for permanent tracking
        DB::table('user_daily_activities')->updateOrInsert(
            [
                'user_id' => $user->id,
                'activity' => $activity,
                'date' => $today
            ],
            [
                'count' => DB::raw('count + 1'),
                'updated_at' => now()
            ]
        );
    }

    /**
     * Get today's activity count
     */
    public function getTodayCount(User $user, string $activity): int
    {
        $today = Carbon::today()->format('Y-m-d');
        $cacheKey = "user_activity:{$user->id}:{$activity}:{$today}";
        
        $cachedCount = Cache::get($cacheKey);
        if ($cachedCount !== null) {
            return $cachedCount;
        }

        // Fallback to database
        $dbCount = DB::table('user_daily_activities')
            ->where('user_id', $user->id)
            ->where('activity', $activity)
            ->where('date', $today)
            ->value('count') ?? 0;

        // Cache the result for 30 minutes to reduce inconsistency window
        Cache::put($cacheKey, $dbCount, Carbon::now()->addMinutes(30));
        
        return $dbCount;
    }

    /**
     * Get user's daily usage summary
     */
    public function getDailyUsageSummary(User $user): array
    {
        $tier = $this->getUserTier($user);
        $limits = $this->getUserLimits($user);
        
        return [
            'tier' => $tier,
            'profile_views' => [
                'used' => $this->getTodayCount($user, 'profile_views'),
                'limit' => $limits['profile_views'],
                'unlimited' => $limits['profile_views'] === -1
            ],
            'messages_sent' => [
                'used' => $this->getTodayCount($user, 'messages_sent'),
                'limit' => $limits['messages'],
                'unlimited' => $limits['messages'] === -1
            ],
            'can_access_contact_details' => $limits['contact_details'],
            'show_ads' => $limits['ads_frequency'] > 0,
            'has_elite_access' => $limits['elite_access'] ?? false,
        ];
    }

    /**
     * Check if free user interaction needs upgrade prompt
     */
    public function checkFreeUserInteraction(User $sender, User $recipient): array
    {
        $senderTier = $this->getUserTier($sender);
        $recipientTier = $this->getUserTier($recipient);

        // If both are free users trying to message each other
        if ($senderTier === self::TIER_FREE && $recipientTier === self::TIER_FREE) {
            return [
                'requires_upgrade' => true,
                'message' => 'Both users are on free plans. One of you needs to upgrade to Basic or higher to start messaging.',
                'upgrade_url' => route('subscription.index')
            ];
        }

        return ['requires_upgrade' => false];
    }

    /**
     * Get upgrade suggestions based on user activity
     */
    public function getUpgradeSuggestions(User $user): array
    {
        $tier = $this->getUserTier($user);
        $todayViews = $this->getTodayCount($user, 'profile_views');
        $todayMessages = $this->getTodayCount($user, 'messages_sent');

        $suggestions = [];

        if ($tier === self::TIER_FREE) {
            $suggestions[] = [
                'reason' => 'messaging',
                'message' => 'Upgrade to Basic to send messages and connect with matches!',
                'tier' => 'basic',
                'priority' => 'high'
            ];

            if ($todayViews >= 40) { // 80% of free limit
                $suggestions[] = [
                    'reason' => 'profile_limit',
                    'message' => 'You\'re close to your daily profile view limit. Upgrade to Basic for more views!',
                    'tier' => 'basic',
                    'priority' => 'medium'
                ];
            }
        }

        if ($tier === self::TIER_BASIC) {
            if ($todayMessages >= 25) { // 80% of basic message limit
                $suggestions[] = [
                    'reason' => 'message_limit',
                    'message' => 'Upgrade to Gold for unlimited profile views and more messages!',
                    'tier' => 'gold',
                    'priority' => 'medium'
                ];
            }
        }

        if ($tier === self::TIER_GOLD) {
            $suggestions[] = [
                'reason' => 'elite_access',
                'message' => 'Upgrade to Platinum for unlimited messaging and access to elite members!',
                'tier' => 'platinum',
                'priority' => 'low'
            ];
        }

        return $suggestions;
    }

    /**
     * Validate profile content for tier restrictions
     */
    public function validateProfileContent(User $user, array $profileData): array
    {
        $tier = $this->getUserTier($user);
        $errors = [];

        // Free users cannot include contact information
        if ($tier === self::TIER_FREE) {
            $restrictedFields = ['phone', 'email', 'whatsapp', 'telegram', 'instagram'];
            
            foreach ($restrictedFields as $field) {
                if (!empty($profileData[$field])) {
                    $errors[] = "Free users cannot include {$field} in their profile. Upgrade to Basic to add contact details.";
                }
            }

            // Check bio and other text fields for contact patterns
            $textFields = ['bio', 'about', 'interests'];
            $contactPatterns = [
                '/\b\d{10,15}\b/', // Phone numbers
                '/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/', // Email
                '/@\w+/', // Social media handles
                '/whatsapp|telegram|instagram|snapchat/i' // Platform mentions
            ];

            foreach ($textFields as $field) {
                if (!empty($profileData[$field])) {
                    foreach ($contactPatterns as $pattern) {
                        if (preg_match($pattern, $profileData[$field])) {
                            $errors[] = "Free users cannot include contact information in {$field}. Upgrade to Basic to share contact details.";
                            break;
                        }
                    }
                }
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Get tier display information
     */
    public function getTierInfo(string $tier): array
    {
        $info = [
            self::TIER_FREE => [
                'name' => 'Free',
                'price_naira' => 0,
                'price_usd' => 0,
                'color' => 'gray',
                'badge' => 'Free User'
            ],
            self::TIER_BASIC => [
                'name' => 'Basic',
                'price_naira' => 8000,
                'price_usd' => 10,
                'color' => 'blue',
                'badge' => 'Basic Member'
            ],
            self::TIER_GOLD => [
                'name' => 'Gold',
                'price_naira' => 15000,
                'price_usd' => 15,
                'color' => 'yellow',
                'badge' => 'Gold Member'
            ],
            self::TIER_PLATINUM => [
                'name' => 'Platinum',
                'price_naira' => 25000,
                'price_usd' => 25,
                'color' => 'purple',
                'badge' => 'Platinum Elite'
            ]
        ];

        return $info[$tier] ?? $info[self::TIER_FREE];
    }
} 
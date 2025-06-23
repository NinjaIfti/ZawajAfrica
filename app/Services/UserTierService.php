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

        // Check if subscription has expired - update status in real-time for consistency
        // Use UTC timezone explicitly to prevent timezone-related issues
        if ($user->subscription_expires_at && $user->subscription_expires_at->setTimezone('UTC')->isPast()) {
            // Update the user's subscription status immediately for consistency
            try {
                $user->update(['subscription_status' => 'expired']);
            } catch (\Exception $e) {
                // Log the error but continue - the cleanup command will handle it later
                \Log::warning("Failed to update expired subscription status for user {$user->id}: " . $e->getMessage());
            }
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
     * Check if user can view profiles (does not record activity)
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
     * Record profile view if allowed and return status
     */
    public function recordProfileView(User $user): array
    {
        $status = $this->canViewProfile($user);
        
        if ($status['allowed']) {
            $this->recordActivity($user, 'profile_views');
            
            // Update the status with new counts
            if ($status['remaining'] !== -1) {
                $status['remaining'] = max(0, $status['remaining'] - 1);
                $status['used'] = $status['used'] + 1;
            }
        }
        
        return $status;
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

    // Valid activity types
    private array $validActivityTypes = [
        'profile_views',
        'messages_sent',
        'likes_sent',
        'matches_created',
        'profile_updates'
    ];

    /**
     * Record user activity for daily tracking
     */
    public function recordActivity(User $user, string $activity): void
    {
        // Validate activity type
        if (!in_array($activity, $this->validActivityTypes)) {
            throw new \InvalidArgumentException("Invalid activity type: {$activity}");
        }

        $today = Carbon::today()->format('Y-m-d');
        $tier = $this->getUserTier($user);
        $limits = $this->getUserLimits($user);

        // Clear cache first to ensure consistency
        $cacheKey = "user_activity:{$user->id}:{$activity}:{$today}";
        Cache::forget($cacheKey);

        // Log activity recording for monitoring
        \Log::info("Recording user activity: {$activity}", [
            'user_id' => $user->id,
            'tier' => $tier,
            'activity' => $activity,
            'date' => $today,
            'daily_limit' => $limits[$activity === 'messages_sent' ? 'messages' : $activity] ?? null
        ]);

        try {
            // Use database transaction to prevent race conditions
            DB::transaction(function () use ($user, $activity, $today) {
            // Database operation with error handling
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
            });
        } catch (\Exception $e) {
            // Log database error
            \Log::error("Database operation failed for activity tracking: " . $e->getMessage(), [
                'user_id' => $user->id,
                'activity' => $activity,
                'tier' => $tier
            ]);
            throw $e; // Re-throw as this is critical
        }
    }

    /**
     * Get today's activity count
     */
    public function getTodayCount(User $user, string $activity): int
    {
        $today = Carbon::today()->format('Y-m-d');
        $cacheKey = "user_activity:{$user->id}:{$activity}:{$today}";
        
        $cachedCount = null;
        try {
        $cachedCount = Cache::get($cacheKey);
        } catch (\Exception $e) {
            \Log::warning("Cache get operation failed for activity count: " . $e->getMessage(), [
                'user_id' => $user->id,
                'activity' => $activity,
                'cache_key' => $cacheKey
            ]);
        }
        
        if ($cachedCount !== null) {
            return $cachedCount;
        }

        // Fallback to database
        $dbCount = DB::table('user_daily_activities')
            ->where('user_id', $user->id)
            ->where('activity', $activity)
            ->where('date', $today)
            ->value('count') ?? 0;

        // Cache the result for 5 minutes to minimize inconsistency window
        try {
            Cache::put($cacheKey, $dbCount, Carbon::now()->addMinutes(5));
        } catch (\Exception $e) {
            \Log::warning("Cache put operation failed for activity count: " . $e->getMessage(), [
                'user_id' => $user->id,
                'activity' => $activity,
                'cache_key' => $cacheKey,
                'count' => $dbCount
            ]);
        }
        
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
        
        // Free users cannot message other free users
        if ($senderTier === self::TIER_FREE && $recipientTier === self::TIER_FREE) {
            return [
                'requires_upgrade' => true,
                'message' => 'Free users cannot message other free users. Upgrade to Basic to start conversations!',
                'upgrade_url' => route('subscription.index'),
                'suggested_tier' => self::TIER_BASIC,
                'reason' => 'free_to_free_restriction'
            ];
        }
        
        // Free sender trying to message paid user - check if they have match
        if ($senderTier === self::TIER_FREE && $recipientTier !== self::TIER_FREE) {
            // Check if they have an active match
            $hasMatch = \App\Models\UserMatch::areMatched($sender->id, $recipient->id);
            
            if (!$hasMatch) {
                return [
                    'requires_upgrade' => true,
                    'message' => 'Free users can only message matched users. Upgrade to Basic to message anyone!',
                    'upgrade_url' => route('subscription.index'),
                    'suggested_tier' => self::TIER_BASIC,
                    'reason' => 'free_no_match_restriction'
                ];
            }
        }
        
        return [
            'requires_upgrade' => false,
            'allowed' => true
        ];
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
            $restrictedFields = ['phone', 'email', 'whatsapp', 'telegram', 'instagram', 'snapchat', 'tiktok', 'facebook', 'twitter'];
            
            foreach ($restrictedFields as $field) {
                if (!empty($profileData[$field])) {
                    $errors[] = "Free users cannot include {$field} in their profile. Upgrade to Basic to add contact details.";
                }
            }

            // Check bio and other text fields for contact patterns
            $textFields = ['bio', 'about', 'interests', 'looking_for', 'about_me', 'heading'];
            
            // Enhanced contact detection patterns
            $contactPatterns = [
                // Phone numbers (more comprehensive)
                '/(\+?\d{1,4}[\s\-\.]?)?\(?\d{2,4}\)?[\s\-\.]?\d{2,4}[\s\-\.]?\d{2,6}/',
                '/\b\d{10,15}\b/', // Simple numeric sequences
                // Email addresses (with variations)
                '/[a-zA-Z0-9._%+-]+\s*[@at]\s*[a-zA-Z0-9.-]+\s*[\.dot]\s*[a-zA-Z]{2,}/',
                '/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/',
                '/[a-zA-Z0-9._%+-]+\s*\(\s*at\s*\)\s*[a-zA-Z0-9.-]+/',
                // Social media handles and usernames
                '/@\w+/',
                '/\b(?:follow\s+me|add\s+me|contact\s+me)\s+[@on]*\s*\w+/i',
                // Platform mentions (case insensitive)
                '/\b(?:whatsapp|telegram|instagram|snapchat|tiktok|facebook|twitter|discord|skype|viber|wechat|line)\b/i',
                // Common contact phrases
                '/\b(?:call\s+me|text\s+me|dm\s+me|message\s+me|reach\s+me)\b/i',
                '/\b(?:my\s+number|phone\s+number|contact\s+number)\b/i',
                // Website URLs
                '/\b(?:https?:\/\/)?(?:www\.)?[a-zA-Z0-9-]+\.[a-zA-Z]{2,}\b/',
                // Disguised contact info
                '/\b(?:zero|one|two|three|four|five|six|seven|eight|nine)\b.*\b(?:zero|one|two|three|four|five|six|seven|eight|nine)\b/i',
                '/\d+[\s\-\.]*\d+[\s\-\.]*\d+/', // Spaced/separated numbers
            ];

            foreach ($textFields as $field) {
                if (!empty($profileData[$field])) {
                    $fieldContent = strtolower($profileData[$field]);
                    
                    // Sanitize content to catch obfuscated contact info
                    $sanitizedContent = preg_replace('/[^a-z0-9\s]/', '', $fieldContent);
                    $sanitizedContent = preg_replace('/\s+/', ' ', $sanitizedContent);
                    
                    foreach ($contactPatterns as $pattern) {
                        if (preg_match($pattern, $fieldContent) || preg_match($pattern, $sanitizedContent)) {
                            $errors[] = "Free users cannot include contact information in {$field}. Remove phone numbers, emails, social media handles, or contact references. Upgrade to Basic to share contact details.";
                            break; // Only show one error per field
                        }
                    }
                    
                    // Additional check for suspicious number patterns
                    if (preg_match_all('/\d/', $fieldContent, $matches) && count($matches[0]) >= 8) {
                        $numbers = implode('', $matches[0]);
                        if (strlen($numbers) >= 10 && !preg_match('/\b(?:age|year|born|height|weight|cm|ft|inch|pounds|kg)\b/i', $fieldContent)) {
                            $errors[] = "Free users cannot include contact information in {$field}. Upgrade to Basic to share contact details.";
                            break;
                        }
                    }
                }
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'tier' => $tier
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
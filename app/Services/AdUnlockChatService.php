<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdUnlockChatService
{
    protected UserTierService $tierService;
    
    const MESSAGES_PER_AD = 3;
    const AD_WATCH_DURATION = 10; // seconds
    const CREDITS_EXPIRE_HOURS = 24; // Credits expire after 24 hours
    
    public function __construct(UserTierService $tierService)
    {
        $this->tierService = $tierService;
    }

    /**
     * Record that user watched an ad for chat unlock
     */
    public function recordAdWatchForChat(User $user, int $watchDuration): array
    {
        // Validate watch duration
        if ($watchDuration < self::AD_WATCH_DURATION) {
            return [
                'success' => false,
                'error' => 'Ad must be watched for at least ' . self::AD_WATCH_DURATION . ' seconds',
                'required_duration' => self::AD_WATCH_DURATION,
                'actual_duration' => $watchDuration
            ];
        }

        // Only allow free users to use this feature
        if ($this->tierService->getUserTier($user) !== UserTierService::TIER_FREE) {
            return [
                'success' => false,
                'error' => 'This feature is only available for free users',
                'tier' => $this->tierService->getUserTier($user)
            ];
        }

        try {
            // Record the ad watch activity
            $this->tierService->recordActivity($user, 'ads_watched_for_chat');
            
            // Grant message credits
            $this->grantMessageCredits($user, self::MESSAGES_PER_AD);
            
            // Log the successful ad watch
            \Log::info('Ad watched for chat unlock', [
                'user_id' => $user->id,
                'watch_duration' => $watchDuration,
                'credits_granted' => self::MESSAGES_PER_AD,
                'timestamp' => now()->toISOString()
            ]);

            return [
                'success' => true,
                'credits_granted' => self::MESSAGES_PER_AD,
                'total_credits' => $this->getRemainingCredits($user),
                'expires_at' => $this->getCreditExpirationTime($user)
            ];

        } catch (\Exception $e) {
            \Log::error('Failed to record ad watch for chat', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'Failed to process ad watch. Please try again.'
            ];
        }
    }

    /**
     * Grant message credits to user
     */
    protected function grantMessageCredits(User $user, int $credits): void
    {
        $cacheKey = $this->getCreditsCacheKey($user);
        $currentCredits = $this->getRemainingCredits($user);
        $newCredits = $currentCredits + $credits;
        
        // Store credits with expiration
        $expiresAt = now()->addHours(self::CREDITS_EXPIRE_HOURS);
        Cache::put($cacheKey, $newCredits, $expiresAt);
        
        // Also store expiration time separately
        $expirationKey = $this->getExpirationCacheKey($user);
        Cache::put($expirationKey, $expiresAt->toISOString(), $expiresAt);
    }

    /**
     * Get remaining message credits for user
     */
    public function getRemainingCredits(User $user): int
    {
        $cacheKey = $this->getCreditsCacheKey($user);
        return Cache::get($cacheKey, 0);
    }

    /**
     * Check if user has message credits available
     */
    public function hasMessageCredits(User $user): bool
    {
        return $this->getRemainingCredits($user) > 0;
    }

    /**
     * Use one message credit
     */
    public function useMessageCredit(User $user): bool
    {
        $currentCredits = $this->getRemainingCredits($user);
        
        if ($currentCredits <= 0) {
            return false;
        }

        $cacheKey = $this->getCreditsCacheKey($user);
        $newCredits = $currentCredits - 1;
        
        if ($newCredits > 0) {
            // Update credits with same expiration
            $expirationTime = $this->getCreditExpirationTime($user);
            if ($expirationTime) {
                Cache::put($cacheKey, $newCredits, $expirationTime);
            } else {
                Cache::put($cacheKey, $newCredits, now()->addHours(self::CREDITS_EXPIRE_HOURS));
            }
        } else {
            // Remove credits if none left
            Cache::forget($cacheKey);
            Cache::forget($this->getExpirationCacheKey($user));
        }

        // Record the message activity
        try {
            $this->tierService->recordActivity($user, 'ad_unlocked_messages');
        } catch (\Exception $e) {
            \Log::warning('Failed to record ad-unlocked message activity', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }

        \Log::info('Ad-unlocked message credit used', [
            'user_id' => $user->id,
            'remaining_credits' => $newCredits
        ]);

        return true;
    }

    /**
     * Get credit expiration time
     */
    public function getCreditExpirationTime(User $user): ?Carbon
    {
        $expirationKey = $this->getExpirationCacheKey($user);
        $expirationString = Cache::get($expirationKey);
        
        return $expirationString ? Carbon::parse($expirationString) : null;
    }

    /**
     * Get user's ad-unlock chat status
     */
    public function getChatUnlockStatus(User $user): array
    {
        $tier = $this->tierService->getUserTier($user);
        
        // Only relevant for free users
        if ($tier !== UserTierService::TIER_FREE) {
            return [
                'available' => false,
                'reason' => 'feature_not_available_for_paid_users',
                'tier' => $tier
            ];
        }

        $remainingCredits = $this->getRemainingCredits($user);
        $expirationTime = $this->getCreditExpirationTime($user);
        $todayWatched = $this->tierService->getTodayCount($user, 'ads_watched_for_chat');
        $todayUsed = $this->tierService->getTodayCount($user, 'ad_unlocked_messages');

        return [
            'available' => true,
            'remaining_credits' => $remainingCredits,
            'has_credits' => $remainingCredits > 0,
            'credits_expire_at' => $expirationTime?->toISOString(),
            'messages_per_ad' => self::MESSAGES_PER_AD,
            'required_watch_duration' => self::AD_WATCH_DURATION,
            'today_stats' => [
                'ads_watched' => $todayWatched,
                'messages_sent' => $todayUsed
            ]
        ];
    }

    /**
     * Clear expired credits (cleanup method)
     */
    public function clearExpiredCredits(User $user): void
    {
        $expirationTime = $this->getCreditExpirationTime($user);
        
        if ($expirationTime && $expirationTime->isPast()) {
            Cache::forget($this->getCreditsCacheKey($user));
            Cache::forget($this->getExpirationCacheKey($user));
            
            \Log::info('Expired ad-unlock credits cleared', [
                'user_id' => $user->id,
                'expired_at' => $expirationTime->toISOString()
            ]);
        }
    }

    /**
     * Get cache key for user credits
     */
    protected function getCreditsCacheKey(User $user): string
    {
        return "ad_unlock_chat_credits:{$user->id}";
    }

    /**
     * Get cache key for credit expiration
     */
    protected function getExpirationCacheKey(User $user): string
    {
        return "ad_unlock_chat_expiration:{$user->id}";
    }

    /**
     * Get daily limit for ad watching (prevent abuse)
     */
    public function getDailyAdWatchLimit(): int
    {
        return 10; // Max 10 ads per day for chat unlock
    }

    /**
     * Check if user can watch more ads today
     */
    public function canWatchMoreAds(User $user): bool
    {
        $todayWatched = $this->tierService->getTodayCount($user, 'ads_watched_for_chat');
        return $todayWatched < $this->getDailyAdWatchLimit();
    }

    /**
     * Get remaining ad watches for today
     */
    public function getRemainingAdWatches(User $user): int
    {
        $todayWatched = $this->tierService->getTodayCount($user, 'ads_watched_for_chat');
        return max(0, $this->getDailyAdWatchLimit() - $todayWatched);
    }
}

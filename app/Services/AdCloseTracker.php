<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class AdCloseTracker
{
    const CLOSE_DELAY_MINUTES = 2.5; // 2.5 minutes delay after closing an ad

    /**
     * Record that a user closed an ad
     */
    public function recordAdClose(User $user, string $adType = 'general'): void
    {
        $key = "ad_close_delay:{$user->id}:{$adType}";
        $expiresAt = now()->addMinutes(self::CLOSE_DELAY_MINUTES);
        
        Cache::put($key, $expiresAt->toISOString(), $expiresAt);
    }

    /**
     * Check if user is within the delay period after closing an ad
     */
    public function isInCloseDelay(User $user, string $adType = 'general'): bool
    {
        $key = "ad_close_delay:{$user->id}:{$adType}";
        $closeTime = Cache::get($key);
        
        if (!$closeTime) {
            return false;
        }
        
        return Carbon::parse($closeTime)->isFuture();
    }

    /**
     * Get remaining delay time in seconds
     */
    public function getRemainingDelaySeconds(User $user, string $adType = 'general'): int
    {
        $key = "ad_close_delay:{$user->id}:{$adType}";
        $closeTime = Cache::get($key);
        
        if (!$closeTime) {
            return 0;
        }
        
        $endTime = Carbon::parse($closeTime);
        return $endTime->isFuture() ? now()->diffInSeconds($endTime) : 0;
    }

    /**
     * Clear the delay (useful for testing or admin overrides)
     */
    public function clearDelay(User $user, string $adType = 'general'): void
    {
        $key = "ad_close_delay:{$user->id}:{$adType}";
        Cache::forget($key);
    }

    /**
     * Check if user should see ads (respects close delay)
     */
    public function canShowAd(User $user, string $adType = 'general'): bool
    {
        return !$this->isInCloseDelay($user, $adType);
    }
} 
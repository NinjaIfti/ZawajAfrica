<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AdCloseTracker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class AdsterraController extends Controller
{
    protected AdCloseTracker $closeTracker;

    public function __construct(AdCloseTracker $closeTracker)
    {
        $this->closeTracker = $closeTracker;
    }

    /**
     * Record that a user closed an ad
     */
    public function recordAdClose(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }

            $request->validate([
                'ad_type' => 'string|nullable',
                'zone_name' => 'string|nullable'
            ]);

            $adType = $request->input('ad_type', 'general');
            
            // Record the ad close with 2.5 minute delay
            $this->closeTracker->recordAdClose($user, $adType);

            return response()->json([
                'success' => true,
                'message' => 'Ad close recorded',
                'delay_minutes' => AdCloseTracker::CLOSE_DELAY_MINUTES,
                'next_ad_available_at' => now()->addMinutes(AdCloseTracker::CLOSE_DELAY_MINUTES)->toISOString()
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to record ad close', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'error' => 'Failed to record ad close'
            ], 500);
        }
    }

    /**
     * Check if user can see ads (respects close delay)
     */
    public function canShowAd(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json(['can_show' => true]); // Guests can see ads
            }

            $adType = $request->input('ad_type', 'general');
            $canShow = $this->closeTracker->canShowAd($user, $adType);
            $remainingSeconds = $this->closeTracker->getRemainingDelaySeconds($user, $adType);

            return response()->json([
                'can_show' => $canShow,
                'remaining_delay_seconds' => $remainingSeconds,
                'delay_minutes' => AdCloseTracker::CLOSE_DELAY_MINUTES
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to check ad availability', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'can_show' => false,
                'error' => 'Failed to check ad availability'
            ], 500);
        }
    }
} 
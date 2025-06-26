<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Services\UserTierService;

class PhotoBlurController extends Controller
{
    protected $tierService;

    public function __construct(UserTierService $tierService)
    {
        $this->tierService = $tierService;
    }

    /**
     * Handle photo unblur request
     */
    public function unblur(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $currentUser = Auth::user();
        $targetUser = User::find($request->user_id);

        if (!$targetUser) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        // Check if target user has photos blurred
        if (!$targetUser->photos_blurred) {
            return response()->json([
                'success' => true,
                'message' => 'Photos are already visible'
            ]);
        }

        // Get current user's tier
        $userTier = $this->tierService->getUserTier($currentUser);
        $tierLimits = $this->getTierLimits($userTier);

        // Check daily photo view limit
        $today = now()->format('Y-m-d');
        $todayViews = DB::table('photo_views')
            ->where('viewer_id', $currentUser->id)
            ->whereDate('created_at', $today)
            ->count();

        if ($todayViews >= $tierLimits['daily_photo_views']) {
            return response()->json([
                'success' => false,
                'message' => 'Daily photo view limit reached. Upgrade your plan to view more photos.'
            ], 429);
        }

        // Check if user already viewed this person's photos today
        $alreadyViewed = DB::table('photo_views')
            ->where('viewer_id', $currentUser->id)
            ->where('target_user_id', $targetUser->id)
            ->whereDate('created_at', $today)
            ->exists();

        if (!$alreadyViewed) {
            // Record the photo view
            DB::table('photo_views')->insert([
                'viewer_id' => $currentUser->id,
                'target_user_id' => $targetUser->id,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Photo unlocked successfully',
            'remaining_views' => max(0, $tierLimits['daily_photo_views'] - $todayViews - 1)
        ]);
    }

    /**
     * Toggle user's own photo blur setting
     */
    public function toggleBlur(Request $request)
    {
        $request->validate([
            'enabled' => 'required|boolean',
            'mode' => 'sometimes|string|in:manual,auto_unlock'
        ]);

        $user = Auth::user();
        $user->photos_blurred = $request->enabled;
        
        // Update blur mode if provided
        if ($request->has('mode')) {
            $user->photo_blur_mode = $request->mode;
        }
        
        $user->save();

        return response()->json([
            'success' => true,
            'enabled' => $user->photos_blurred,
            'mode' => $user->photo_blur_mode,
            'message' => $request->enabled ? 'Photos are now private' : 'Photos are now visible'
        ]);
    }

    /**
     * Get blur settings for user
     */
    public function getBlurSettings()
    {
        $user = Auth::user();
        
        return response()->json([
            'photos_blurred' => $user->photos_blurred,
            'photo_blur_mode' => $user->photo_blur_mode,
            'photo_blur_permissions' => json_decode($user->photo_blur_permissions, true) ?? []
        ]);
    }

    /**
     * Get tier limits for photo viewing
     */
    private function getTierLimits($tier)
    {
        $limits = [
            'free' => [
                'daily_photo_views' => 3
            ],
            'basic' => [
                'daily_photo_views' => 10
            ],
            'gold' => [
                'daily_photo_views' => 25
            ],
            'platinum' => [
                'daily_photo_views' => 999999 // Unlimited
            ]
        ];

        return $limits[$tier] ?? $limits['free'];
    }

    /**
     * Check if user can view target user's photos
     */
    public function canViewPhotos(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $currentUser = Auth::user();
        $targetUser = User::find($request->user_id);

        // If photos are not blurred, always can view
        if (!$targetUser->photos_blurred) {
            return response()->json([
                'can_view' => true,
                'reason' => 'Photos are public'
            ]);
        }

        // Check if already viewed today
        $today = now()->format('Y-m-d');
        $alreadyViewed = DB::table('photo_views')
            ->where('viewer_id', $currentUser->id)
            ->where('target_user_id', $targetUser->id)
            ->whereDate('created_at', $today)
            ->exists();

        if ($alreadyViewed) {
            return response()->json([
                'can_view' => true,
                'reason' => 'Already viewed today'
            ]);
        }

        // Check daily limit
        $userTier = $this->tierService->getUserTier($currentUser);
        $tierLimits = $this->getTierLimits($userTier);
        
        $todayViews = DB::table('photo_views')
            ->where('viewer_id', $currentUser->id)
            ->whereDate('created_at', $today)
            ->count();

        $canView = $todayViews < $tierLimits['daily_photo_views'];

        return response()->json([
            'can_view' => $canView,
            'remaining_views' => max(0, $tierLimits['daily_photo_views'] - $todayViews),
            'tier' => $userTier,
            'reason' => $canView ? 'Within daily limit' : 'Daily limit exceeded'
        ]);
    }
} 
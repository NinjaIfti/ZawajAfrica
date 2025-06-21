<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\UserTierService;
use Illuminate\Support\Facades\Auth;

class TierAccessMiddleware
{
    protected UserTierService $tierService;

    public function __construct(UserTierService $tierService)
    {
        $this->tierService = $tierService;
    }

    public function handle(Request $request, Closure $next, string $restriction = null)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        switch ($restriction) {
            case 'profile_view':
                $canView = $this->tierService->canViewProfile($user);
                if (!$canView['allowed']) {
                    return response()->json([
                        'error' => 'Daily profile view limit reached',
                        'limit' => $canView['limit'],
                        'upgrade_prompt' => true
                    ], 429);
                }
                // Record the activity
                $this->tierService->recordActivity($user, 'profile_views');
                break;

            case 'messaging':
                $canMessage = $this->tierService->canSendMessage($user);
                if (!$canMessage['allowed']) {
                    return response()->json([
                        'error' => $canMessage['message'],
                        'reason' => $canMessage['reason'],
                        'upgrade_prompt' => true
                    ], 403);
                }
                // Record will be done in the controller after successful send
                break;

            case 'contact_details':
                $limits = $this->tierService->getUserLimits($user);
                if (!($limits['contact_details'] ?? false)) {
                    return response()->json([
                        'error' => 'Contact details access requires Basic plan or higher',
                        'upgrade_prompt' => true
                    ], 403);
                }
                break;

            case 'elite_access':
                $limits = $this->tierService->getUserLimits($user);
                if (!($limits['elite_access'] ?? false)) {
                    return response()->json([
                        'error' => 'Elite member access requires Platinum membership',
                        'upgrade_prompt' => true
                    ], 403);
                }
                break;
        }

        return $next($request);
    }
} 
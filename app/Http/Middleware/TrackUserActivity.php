<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;

class TrackUserActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (Auth::check()) {
            $user = Auth::user();
            
            // Update last activity timestamp
            $now = Carbon::now();
            
            // Only update the timestamp if it's been more than 1 minute since last update
            // This prevents excessive database writes while keeping status more current
            if (!$user->last_activity_at || $now->diffInMinutes($user->last_activity_at) >= 1) {
                $user->last_activity_at = $now;
                $user->save();
            }
        }

        return $response;
    }
} 
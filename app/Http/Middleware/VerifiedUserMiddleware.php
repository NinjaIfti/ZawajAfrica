<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifiedUserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip verification check for admin users
        if ($request->user() && $request->user()->email === 'admin@zawagafrica.com') {
            return $next($request);
        }
        
        // If user is not verified, redirect to verification intro
        if ($request->user() && !$request->user()->is_verified) {
            // If user has a pending verification, show a message
            if ($request->user()->verification && $request->user()->verification->status === 'pending') {
                return redirect()->route('verification.pending');
            }
            
            // Otherwise, start the verification process
            return redirect()->route('verification.intro');
        }

        return $next($request);
    }
} 
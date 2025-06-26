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
        
        // Allow all users to proceed regardless of verification status
        // Verification is now optional - users can skip it and still access the site
        // Admin can still review submitted verification documents
        
        return $next($request);
    }
} 
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFreshCsrfToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Add CSRF token to response headers for JavaScript access
        if ($request->expectsJson() || $request->is('api/*')) {
            $response->headers->set('X-CSRF-TOKEN', csrf_token());
        }

        // For Inertia responses, ensure CSRF token is always fresh
        if ($request->header('X-Inertia')) {
            // Regenerate CSRF token on login/logout routes
            if ($request->routeIs('login') || $request->routeIs('logout')) {
                $request->session()->regenerateToken();
            }
            
            $response->headers->set('X-CSRF-TOKEN', csrf_token());
        }

        return $response;
    }
} 
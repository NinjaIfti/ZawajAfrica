<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated and is admin
        if (!$request->user() || $request->user()->email !== 'admin@zawagafrica.com') {
            if ($request->expectsJson() || $request->is('admin/*')) {
                abort(403, 'Unauthorized');
            }
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
} 
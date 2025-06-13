<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        
        // Ensure location is set for the user data
        if ($user && empty($user->location)) {
            $location = '';
            if ($user->city && trim($user->city) !== '') {
                $location .= trim($user->city);
            }
            if ($user->state && trim($user->state) !== '') {
                $location .= ($location ? ', ' : '') . trim($user->state);
            }
            if ($user->country && trim($user->country) !== '') {
                $location .= ($location ? ', ' : '') . trim($user->country);
            }
            $user->location = !empty($location) ? $location : 'Location not set';
        }
        
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
            ],
        ];
    }
}

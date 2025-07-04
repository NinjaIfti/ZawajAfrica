<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Services\AdSenseService;

class HandleInertiaRequests extends Middleware
{
    protected AdSenseService $adSenseService;

    public function __construct(AdSenseService $adSenseService)
    {
        $this->adSenseService = $adSenseService;
    }

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
        
        // Generate a virtual location property for the user from city, state, country
        if ($user) {
            // Don't modify the actual user model, just add a property for the view
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
            // Add as a virtual property, not actually saving to database
            $user->setAttribute('location', !empty($location) ? $location : 'Location not set');
        }
        
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
            ],
            'flash' => [
                'payment_success' => $request->session()->get('payment_success'),
                'payment_type' => $request->session()->get('payment_type'),
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
                'csrf_token' => $request->session()->get('csrf_token'),
            ],
            'csrf_token' => csrf_token(),
            // Always include a fresh CSRF token
            'csrf' => [
                'token' => csrf_token(),
                'header' => 'X-CSRF-TOKEN',
            ],
            // AdSense configuration for frontend
            'adsense' => [
                'config' => $this->adSenseService->getAdSenseConfig($user),
                'show_on_page' => $this->adSenseService->shouldShowAdsOnPage($request),
                'consent' => $this->adSenseService->getConsentStatus($request),
            ],
        ];
    }
}

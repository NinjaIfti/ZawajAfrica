<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();

        if ($user) {
            $location = collect([$user->city, $user->state, $user->country])
                ->filter(fn ($value) => filled(trim((string) $value)))
                ->map(fn ($value) => trim((string) $value))
                ->implode(', ');
            $user->setAttribute('location', $location ?: 'Location not set');
        }

        $product = config('product');

        return [
            ...parent::share($request),
            'auth' => ['user' => $user],
            'product' => [
                'name' => $product['name'],
                'tagline' => $product['tagline'],
                'logos' => $product['logos'],
                'colors' => $product['colors'],
                'locale' => $product['locale'],
                'timezone' => $product['timezone'],
                'defaults' => $product['defaults'],
                'supported' => $product['supported'],
                'legal' => $product['legal'],
                'features' => $product['features'],
                'payment_providers' => $product['payment_providers'],
            ],
            'flash' => [
                'payment_success' => $request->session()->get('payment_success'),
                'payment_type' => $request->session()->get('payment_type'),
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
                'csrf_token' => $request->session()->get('csrf_token'),
            ],
            'csrf_token' => csrf_token(),
            'csrf' => [
                'token' => csrf_token(),
                'header' => 'X-CSRF-TOKEN',
            ],
        ];
    }
}

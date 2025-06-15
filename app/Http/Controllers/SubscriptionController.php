<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    /**
     * Display the subscription plans page.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Format profile photo URL if it exists
        if ($user->profile_photo) {
            $user->profile_photo = asset('storage/' . $user->profile_photo);
        }
        
        // Define subscription plans
        $plans = [
            'male' => [
                [
                    'name' => 'Basic',
                    'price_usd' => 5,
                    'price_naira' => '7,000',
                    'features' => [
                        'Unlock Messages',
                        'Say Goodbye to Ads',
                        'Rank Above Other Members',
                        'Get Better Match'
                    ]
                ],
                [
                    'name' => 'Economy',
                    'price_usd' => 10,
                    'price_naira' => '12,000',
                    'features' => [
                        'Unlock Messages',
                        'Say Goodbye to Ads',
                        'Rank Above Other Members',
                        'Get Better Match'
                    ]
                ],
                [
                    'name' => 'VIP',
                    'price_usd' => 20,
                    'price_naira' => '20,000',
                    'features' => [
                        'Unlock Messages',
                        'Say Goodbye to Ads',
                        'Rank Above Other Members',
                        'Get Better Match'
                    ]
                ]
            ],
            'female' => [
                [
                    'name' => 'Basic',
                    'price_usd' => 3,
                    'price_naira' => '5,000',
                    'features' => [
                        'Unlock Messages',
                        'Say Goodbye to Ads',
                        'Rank Above Other Members',
                        'Get Better Match'
                    ]
                ],
                [
                    'name' => 'Economy',
                    'price_usd' => 7,
                    'price_naira' => '10,000',
                    'features' => [
                        'Unlock Messages',
                        'Say Goodbye to Ads',
                        'Rank Above Other Members',
                        'Get Better Match'
                    ]
                ],
                [
                    'name' => 'VIP',
                    'price_usd' => 15,
                    'price_naira' => '18,000',
                    'features' => [
                        'Unlock Messages',
                        'Say Goodbye to Ads',
                        'Rank Above Other Members',
                        'Get Better Match'
                    ]
                ]
            ]
        ];
        
        return Inertia::render('Subscription/Index', [
            'user' => $user,
            'plans' => $plans,
            'userGender' => $user->gender ?? 'male'
        ]);
    }
    
    /**
     * Process a subscription purchase.
     */
    public function purchase(Request $request)
    {
        $request->validate([
            'plan' => 'required|string',
            'agreed_to_terms' => 'required|boolean|accepted'
        ]);
        
        // Here you would integrate with your payment processor
        // For now, we'll just return a success message
        
        return back()->with('success', 'Subscription purchased successfully!');
    }
} 
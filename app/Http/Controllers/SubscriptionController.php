<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Services\PaystackService;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    protected $paystackService;

    public function __construct(PaystackService $paystackService)
    {
        $this->paystackService = $paystackService;
    }

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
        
        // Define subscription plans - Updated pricing
        $plans = [
            'male' => [
                [
                    'name' => 'Basic',
                    'price_usd' => 10,
                    'price_naira' => '8,000',
                    'features' => [
                        'Unlock Messages',
                        'Say Goodbye to Ads',
                        'Rank Above Other Members',
                        'Get Better Match'
                    ]
                ],
                [
                    'name' => 'Gold',
                    'price_usd' => 15,
                    'price_naira' => '15,000',
                    'features' => [
                        'Unlock Messages',
                        'Say Goodbye to Ads',
                        'Rank Above Other Members',
                        'Get Better Match',
                        'Priority Support',
                        'Advanced Filters'
                    ]
                ],
                [
                    'name' => 'Platinum',
                    'price_usd' => 25,
                    'price_naira' => '25,000',
                    'features' => [
                        'Unlock Messages',
                        'Say Goodbye to Ads',
                        'Rank Above Other Members',
                        'Get Better Match',
                        'Priority Support',
                        'Advanced Filters',
                        'VIP Profile Badge',
                        'Unlimited Super Likes'
                    ]
                ]
            ],
            'female' => [
                [
                    'name' => 'Basic',
                    'price_usd' => 10,
                    'price_naira' => '8,000',
                    'features' => [
                        'Unlock Messages',
                        'Say Goodbye to Ads',
                        'Rank Above Other Members',
                        'Get Better Match'
                    ]
                ],
                [
                    'name' => 'Gold',
                    'price_usd' => 15,
                    'price_naira' => '15,000',
                    'features' => [
                        'Unlock Messages',
                        'Say Goodbye to Ads',
                        'Rank Above Other Members',
                        'Get Better Match',
                        'Priority Support',
                        'Advanced Filters'
                    ]
                ],
                [
                    'name' => 'Platinum',
                    'price_usd' => 25,
                    'price_naira' => '25,000',
                    'features' => [
                        'Unlock Messages',
                        'Say Goodbye to Ads',
                        'Rank Above Other Members',
                        'Get Better Match',
                        'Priority Support',
                        'Advanced Filters',
                        'VIP Profile Badge',
                        'Unlimited Super Likes'
                    ]
                ]
            ]
        ];
        
        return Inertia::render('Subscription/Index', [
            'user' => $user,
            'plans' => $plans,
            'userGender' => $user->gender ?? 'male',
            'paystackPublicKey' => $this->paystackService->getPublicKey(),
            'currentSubscription' => [
                'plan' => $user->subscription_plan,
                'status' => $user->subscription_status,
                'expires_at' => $user->subscription_expires_at
            ]
        ]);
    }
    
    /**
     * Show manual payment page for selected plan
     */
    public function showManualPayment(Request $request)
    {
        $user = Auth::user();
        
        // Format profile photo URL if it exists
        if ($user->profile_photo) {
            $user->profile_photo = asset('storage/' . $user->profile_photo);
        }
        
        // Get plan from query parameter or default to Basic
        $planName = $request->query('plan', 'Basic');
        
        // Define subscription plans with pricing
        $planPrices = [
            'Basic' => ['name' => 'Basic', 'naira' => 8000, 'usd' => 10],
            'Gold' => ['name' => 'Gold', 'naira' => 15000, 'usd' => 15],
            'Platinum' => ['name' => 'Platinum', 'naira' => 25000, 'usd' => 25]
        ];
        
        $selectedPlan = $planPrices[$planName] ?? $planPrices['Basic'];
        
        return Inertia::render('Subscription/ManualPayment', [
            'user' => $user,
            'selectedPlan' => $selectedPlan,
            'userGender' => $user->gender ?? 'male',
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
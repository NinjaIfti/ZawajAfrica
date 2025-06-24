<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class SocialAuthController extends Controller
{
    /**
     * Redirect to Google for authentication
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google callback
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            // Check if user already exists
            $user = User::where('email', $googleUser->getEmail())->first();
            
            if ($user) {
                // User exists, log them in
                Auth::login($user);
                
                // Check if the user is an admin
                if ($user->email === 'admin@zawagafrica.com') {
                    return redirect()->route('admin.dashboard')->with('csrf_token', csrf_token());
                }
                
                return redirect()->intended(route('dashboard'))->with('csrf_token', csrf_token());
            } else {
                // Create new user
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'email_verified_at' => now(),
                    'password' => Hash::make(Str::random(24)), // Random password since they're using OAuth
                    'is_verified' => false,
                ]);
                
                // Create a blank profile for the user
                $user->profile()->create([
                    'religion' => 'Muslim', // Default, they can update later
                ]);
                
                Auth::login($user);
                
                // Redirect to verification process for new users
                return redirect()->route('verification.intro');
            }
            
        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors(['error' => 'Unable to login with Google. Please try again.']);
        }
    }

    /**
     * Redirect to Apple for authentication (placeholder)
     */
    public function redirectToApple()
    {
        // Apple OAuth would be implemented here
        return redirect()->route('login')->withErrors(['error' => 'Apple login coming soon!']);
    }

    /**
     * Handle Apple callback (placeholder)
     */
    public function handleAppleCallback()
    {
        // Apple OAuth callback would be implemented here
        return redirect()->route('login')->withErrors(['error' => 'Apple login coming soon!']);
    }
} 
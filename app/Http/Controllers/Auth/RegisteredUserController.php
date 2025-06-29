<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Events\UserRegistered;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'gender' => 'nullable|string|in:male,female',
            'interested_in' => 'nullable|string|in:male,female',
            'dob_day' => 'nullable|string|max:2',
            'dob_month' => 'nullable|string|max:3',
            'dob_year' => 'nullable|string|max:4',
            'country' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
        ]);

        // We'll generate location on-the-fly, not store it directly
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'gender' => $request->gender,
            'interested_in' => $request->interested_in,
            'dob_day' => $request->dob_day,
            'dob_month' => $request->dob_month,
            'dob_year' => $request->dob_year,
            'country' => $request->country,
            'state' => $request->state,
            'city' => $request->city,
            'is_verified' => false,
        ]);
        
        // Create a blank profile for the user
        $user->profile()->create([
            'religion' => $user->gender === 'male' ? 'Muslim' : 'Muslimah',
        ]);

        event(new Registered($user));
        
        // Fire our custom event for AI welcome email
        event(new UserRegistered($user));

        Auth::login($user);

        // Redirect to verification process instead of dashboard
        return redirect(route('verification.intro', absolute: false));
    }
}

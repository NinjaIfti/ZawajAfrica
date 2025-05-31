<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Profile;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();
        
        // Ensure user has a profile
        if (!$user->profile) {
            $user->profile()->create();
        }
        
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => session('status'),
            'profile' => $user->profile,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Display the user's detailed profile form.
     */
    public function editDetails(Request $request): Response
    {
        $user = $request->user();
        
        // Ensure user has a profile
        if (!$user->profile) {
            $user->profile()->create();
        }
        
        return Inertia::render('Profile/EditDetails', [
            'profile' => $user->profile,
            'user' => $user->only(['name', 'gender', 'dob_day', 'dob_month', 'dob_year', 'country', 'state', 'city'])
        ]);
    }
    
    /**
     * Update the user's detailed profile information.
     */
    public function updateDetails(Request $request): RedirectResponse
    {
        $user = $request->user();
        
        $validated = $request->validate([
            // Personal Information
            'bio' => 'nullable|string|max:1000',
            'height' => 'nullable|string|max:10',
            'body_type' => 'nullable|string|max:50',
            'ethnicity' => 'nullable|string|max:50',
            'religion' => 'nullable|string|max:50',
            'religious_values' => 'nullable|string|max:50',
            'marital_status' => 'nullable|string|max:50',
            'children' => 'nullable|integer',
            'want_children' => 'nullable|string|max:50',
            
            // Education & Career
            'education_level' => 'nullable|string|max:50',
            'field_of_study' => 'nullable|string|max:100',
            'occupation' => 'nullable|string|max:100',
            'income_range' => 'nullable|string|max:50',
            
            // Lifestyle & Habits
            'smoking' => 'nullable|string|max:50',
            'drinking' => 'nullable|string|max:50',
            'prayer_frequency' => 'nullable|string|max:50',
            'hijab_wearing' => 'nullable|boolean',
            'beard_type' => 'nullable|string|max:50',
            
            // Matching Preferences
            'age_preference_min' => 'nullable|string|max:3',
            'age_preference_max' => 'nullable|string|max:3',
            'about_match' => 'nullable|string|max:1000',
            
            // Profile Picture
            'profile_picture' => 'nullable|image|max:2048',
        ]);
        
        // Handle profile picture upload if present
        if ($request->hasFile('profile_picture')) {
            // Delete old profile picture if exists
            if ($user->profile && $user->profile->profile_picture) {
                Storage::disk('public')->delete($user->profile->profile_picture);
            }
            
            // Store new profile picture
            $path = $request->file('profile_picture')->store('profile-pictures', 'public');
            $validated['profile_picture'] = $path;
        }
        
        // Update or create profile
        $user->createOrUpdateProfile($validated);
        
        return Redirect::route('profile.edit.details')->with('status', 'profile-details-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // Delete profile picture if exists
        if ($user->profile && $user->profile->profile_picture) {
            Storage::disk('public')->delete($user->profile->profile_picture);
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
    
    /**
     * View a user's public profile.
     */
    public function show($id)
    {
        $user = \App\Models\User::with('profile')->findOrFail($id);
        
        // Calculate age from DOB
        $age = null;
        if ($user->dob_day && $user->dob_month && $user->dob_year) {
            $age = $user->age;
        }
        
        return Inertia::render('Profile/Show', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'gender' => $user->gender,
                'age' => $age,
                'city' => $user->city,
                'state' => $user->state,
                'country' => $user->country,
            ],
            'profile' => $user->profile,
        ]);
    }
}

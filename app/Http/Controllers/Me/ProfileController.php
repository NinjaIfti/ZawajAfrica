<?php

namespace App\Http\Controllers\Me;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Display the user's profile.
     */
    public function index()
    {
        $user = Auth::user();
        
        return Inertia::render('Me/Profile', [
            'user' => $user
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        
        // Validate request
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'location' => ['sometimes', 'string', 'nullable', 'max:255'],
            'appearance' => ['sometimes', 'array'],
            'lifestyle' => ['sometimes', 'array'],
            'background' => ['sometimes', 'array'],
            'about' => ['sometimes', 'array'],
        ]);
        
        // Log the request data for debugging
        Log::info('Profile update request', [
            'user_id' => $user->id,
            'data' => $validated
        ]);

        // Update user's basic info
        if (isset($validated['name'])) {
            $user->name = $validated['name'];
        }
        
        if (isset($validated['location'])) {
            $user->location = $validated['location'];
        }
        
        // Update appearance data
        if (isset($validated['appearance'])) {
            // Assuming you have a proper relationship set up
            // If not, you might need to create a new model for this
            $user->appearance()->updateOrCreate(
                ['user_id' => $user->id],
                $validated['appearance']
            );
        }
        
        // Update lifestyle data
        if (isset($validated['lifestyle'])) {
            $user->lifestyle()->updateOrCreate(
                ['user_id' => $user->id],
                $validated['lifestyle']
            );
        }
        
        // Update background data
        if (isset($validated['background'])) {
            $user->background()->updateOrCreate(
                ['user_id' => $user->id],
                $validated['background']
            );
        }
        
        // Update about data
        if (isset($validated['about'])) {
            $user->about()->updateOrCreate(
                ['user_id' => $user->id],
                $validated['about']
            );
        }
        
        // Save the user
        $user->save();
        
        // Return the updated user data
        return back()->with([
            'message' => 'Profile updated successfully',
            'user' => $user->fresh()
        ]);
    }

    /**
     * Update the user's profile photo.
     */
    public function updatePhoto(Request $request)
    {
        $user = Auth::user();
        
        // Validate the uploaded file
        $request->validate([
            'profile_photo' => ['required', 'image', 'max:2048'], // 2MB max
        ]);
        
        if ($request->hasFile('profile_photo')) {
            $file = $request->file('profile_photo');
            
            // Delete old profile photo if it exists
            if ($user->profile_photo && Storage::exists($user->profile_photo)) {
                Storage::delete($user->profile_photo);
            }
            
            // Store the new photo
            $path = $file->store('profile-photos', 'public');
            
            // Update user record with new photo path
            $user->profile_photo = $path;
            $user->save();
            
            return back()->with([
                'message' => 'Profile photo updated successfully',
                'user' => $user->fresh()
            ]);
        }
        
        return back()->with('error', 'Failed to upload profile photo');
    }
}

<?php

namespace App\Http\Controllers\Me;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    /**
     * Display the user's profile.
     */
    public function index()
    {
        $user = Auth::user()->load(['appearance', 'lifestyle', 'background', 'about', 'overview', 'others']);
        
        // Ensure the profile photo URL is fully qualified
        if ($user->profile_photo) {
            $user->profile_photo = asset('storage/' . $user->profile_photo);
        }
        
        // Get user tier information
        $tierService = app(\App\Services\UserTierService::class);
        $userTier = $tierService->getUserTier($user);
        $user->tier = $userTier;
        
        return Inertia::render('Me/Profile', [
            'user' => $user
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Log ALL request data for debugging
            Log::info('Profile update request - ALL DATA', [
                'user_id' => $user->id,
                'all_data' => $request->all(),
                'is_ajax' => $request->ajax(),
                'wants_json' => $request->wantsJson()
            ]);
            
            // Explicitly remove location if it's somehow in the request
            $requestData = $request->except(['location']);
            
            // Validate request using standard validation
            $validated = Validator::make($requestData, [
                'name' => ['sometimes', 'string', 'max:255'],
                'gender' => ['sometimes', 'string', 'nullable', 'in:Male,Female'],
                'dob_day' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:31'],
                'dob_month' => ['sometimes', 'nullable', 'string', 'in:Jan,Feb,Mar,Apr,May,Jun,Jul,Aug,Sep,Oct,Nov,Dec'],
                'dob_year' => ['sometimes', 'nullable', 'integer', 'min:1950', 'max:2010'],
                'country' => ['sometimes', 'string', 'nullable', 'max:255'],
                'state' => ['sometimes', 'string', 'nullable', 'max:255'],
                'city' => ['sometimes', 'string', 'nullable', 'max:255'],
                'appearance' => ['sometimes', 'array'],
                'lifestyle' => ['sometimes', 'array'],
                'background' => ['sometimes', 'array'],
                'about' => ['sometimes', 'array'],
                'overview' => ['sometimes', 'array'],
                'others' => ['sometimes', 'array'],
            ])->validate();
            
            // Log the validated data for debugging
            Log::info('Profile update request - VALIDATED DATA', [
                'user_id' => $user->id,
                'validated_data' => $validated
            ]);
    
            // Update user's basic info
            if (isset($validated['name'])) {
                $user->name = $validated['name'];
            }
            
            if (isset($validated['gender'])) {
                $user->gender = $validated['gender'];
            }
            
            if (isset($validated['dob_day'])) {
                $user->dob_day = $validated['dob_day'];
            }
            
            if (isset($validated['dob_month'])) {
                $user->dob_month = $validated['dob_month'];
            }
            
            if (isset($validated['dob_year'])) {
                $user->dob_year = $validated['dob_year'];
            }
            
            // Update location components if provided
            if (isset($validated['country'])) {
                $user->country = $validated['country'];
            }
            
            if (isset($validated['state'])) {
                $user->state = $validated['state'];
            }
            
            if (isset($validated['city'])) {
                $user->city = $validated['city'];
            }
            
            // Update appearance data
            if (isset($validated['appearance'])) {
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
            
            // Update overview data
            if (isset($validated['overview'])) {
                $user->overview()->updateOrCreate(
                    ['user_id' => $user->id],
                    $validated['overview']
                );
            }
            
            // Update others data
            if (isset($validated['others'])) {
                $user->others()->updateOrCreate(
                    ['user_id' => $user->id],
                    $validated['others']
                );
            }
            
            // Save the user
            $user->save();
            
            // Reload the user with relationships
            $user = $user->fresh(['appearance', 'lifestyle', 'background', 'about', 'overview', 'others']);
            
            // Format profile photo URL if it exists
            if ($user->profile_photo) {
                $user->profile_photo = asset('storage/' . $user->profile_photo);
            }
            
            // For all requests, return JSON response
            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'user' => $user
            ]);
        } catch (\Exception $e) {
            // Log the error
            Log::error('Error updating profile', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return error as JSON
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile: ' . $e->getMessage()
            ], 500);
        }
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
            if ($user->profile_photo && Storage::exists('public/' . $user->profile_photo)) {
                Storage::delete('public/' . $user->profile_photo);
            }
            
            // Store the new photo
            $path = $file->store('profile-photos', 'public');
            
            // Update only the profile_photo field using direct query - no location field
            \App\Models\User::where('id', $user->id)
                ->update(['profile_photo' => $path]);
            
            // Reload user and format photo URL
            $user = $user->fresh(['appearance', 'lifestyle', 'background', 'about', 'overview', 'others']);
            $user->profile_photo = asset('storage/' . $path);
            
            // Return Inertia response instead of JSON
            return redirect()->back()->with([
                'success' => 'Profile photo updated successfully',
                'user' => $user
            ]);
        }
        
        // Return Inertia error response
        return redirect()->back()->withErrors([
            'profile_photo' => 'Failed to upload profile photo'
        ]);
    }
}

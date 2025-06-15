<?php

namespace App\Http\Controllers\Me;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class HobbiesController extends Controller
{
    public function index()
    {
        // Load user with interests relationship
        $user = Auth::user()->load('interests');
        
        // Format profile photo URL if it exists
        if ($user->profile_photo) {
            $user->profile_photo = asset('storage/' . $user->profile_photo);
        }
        
        return Inertia::render('Me/Hobbies', [
            'user' => $user
        ]);
    }
    
    public function update(Request $request)
    {
        $user = Auth::user();
        
        // Create or update user interests using the UserInterest model
        $interests = $user->interests()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'entertainment' => $request->entertainment ?? '',
                'food' => $request->food ?? '',
                'music' => $request->music ?? '',
                'sports' => $request->sports ?? ''
            ]
        );
        
        // Check if this is an AJAX request
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Hobbies updated successfully'
            ]);
        }
        
        // For regular form submissions, redirect back to the hobbies page
        return redirect()->route('me.hobbies')->with('success', 'Hobbies updated successfully');
    }
}

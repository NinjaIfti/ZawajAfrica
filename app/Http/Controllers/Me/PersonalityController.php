<?php

namespace App\Http\Controllers\Me;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PersonalityController extends Controller
{
    public function index()
    {
        // Load user with personality relationship
        $user = Auth::user()->load('personality');
        
        // Format profile photo URL if it exists
        if ($user->profile_photo) {
            $user->profile_photo = asset('storage/' . $user->profile_photo);
        }
        
        return Inertia::render('Me/Personality', [
            'user' => $user
        ]);
    }
    
    public function update(Request $request)
    {
        $user = Auth::user();
        
        // Update or create personality data
        $user->personality()->updateOrCreate(
            ['user_id' => $user->id],
            $request->personality
        );
        
        // Check if this is an AJAX request
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Personality traits updated successfully'
            ]);
        }
        
        // For regular form submissions, return an Inertia redirect
        return redirect()->route('me.personality')->with('success', 'Personality traits updated successfully');
    }
}

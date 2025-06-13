<?php

namespace App\Http\Controllers\Me;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class FAQsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Format profile photo URL if it exists
        if ($user->profile_photo) {
            $user->profile_photo = asset('storage/' . $user->profile_photo);
        }
        
        return Inertia::render('Me/FAQs', [
            'user' => $user
        ]);
    }
    
    public function update(Request $request)
    {
        $user = Auth::user();
        
        // FAQs update logic will go here
        
        return response()->json([
            'success' => true,
            'message' => 'FAQs updated successfully'
        ]);
    }
}

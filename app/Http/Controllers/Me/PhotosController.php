<?php

namespace App\Http\Controllers\Me;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\UserPhoto;
use Inertia\Inertia;

class PhotosController extends Controller
{
    /**
     * Display the user's photos page
     */
    public function index()
    {
        $user = Auth::user();
        
        // Load user photos with URLs
        $photos = $user->photos()->get()->map(function($photo) {
            return [
                'id' => $photo->id,
                'url' => $photo->url, // This uses the getUrlAttribute accessor
                'is_primary' => $photo->is_primary,
                'display_order' => $photo->display_order
            ];
        });
        
        // Format profile photo URL if it exists
        if ($user->profile_photo) {
            $user->profile_photo = asset('storage/' . $user->profile_photo);
        }
        
        // Include blur settings in user data
        $user->photos_blurred = $user->photos_blurred ?? false;
        $user->photo_blur_mode = $user->photo_blur_mode ?? 'manual';
        
        return Inertia::render('Me/Photos', [
            'user' => $user,
            'photos' => $photos
        ]);
    }
    
    /**
     * Upload a new photo
     */
    public function upload(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Validate the request
            $request->validate([
                'photo' => 'required|image|max:5120', // 5MB max
                'index' => 'required|integer|min:0',
            ]);
            
            // Handle the file upload
            if ($request->hasFile('photo')) {
                $file = $request->file('photo');
                
                // Create user-specific directory with unique timestamp to prevent collisions
                $userDir = 'user-photos/' . $user->id . '/' . time();
                $path = $file->store($userDir, 'public');
                
                // Get the index and primary flag
                $index = $request->input('index', 0);
                $isPrimary = false; // Default to false
                
                // Create the photo record
                $photo = new UserPhoto([
                    'user_id' => $user->id,
                    'photo_path' => $path,
                    'is_primary' => $isPrimary,
                    'display_order' => $index
                ]);
                
                $photo->save();
                
                // If this is the first photo, make it primary
                $photoCount = UserPhoto::where('user_id', $user->id)->count();
                if ($photoCount === 1) {
                    $photo->is_primary = true;
                    $photo->save();
                }
                
                // If this is the first photo and no profile photo is set, use this as the profile photo
                if (!$user->profile_photo) {
                    $user->profile_photo = $path;
                    $user->save();
                }
                
                // Get all photos for the response
                $photos = $user->photos()->get()->map(function($photo) {
                    return [
                        'id' => $photo->id,
                        'url' => $photo->url,
                        'is_primary' => $photo->is_primary,
                        'display_order' => $photo->display_order
                    ];
                });
                
                return response()->json([
                    'success' => true,
                    'message' => 'Photo uploaded successfully',
                    'photos' => $photos
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'No photo provided'
            ], 400);
            
        } catch (\Exception $e) {
            Log::error('Error uploading photo: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload photo: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Delete a photo
     */
    public function delete($id)
    {
        try {
            $user = Auth::user();
            $photo = UserPhoto::where('id', $id)
                ->where('user_id', $user->id)
                ->first();
            
            if (!$photo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Photo not found'
                ], 404);
            }
            
            // Delete the file from storage
            if (Storage::disk('public')->exists($photo->photo_path)) {
                Storage::disk('public')->delete($photo->photo_path);
            }
            
            // If this was the profile photo, clear the profile photo field
            if ($user->profile_photo === $photo->photo_path) {
                $user->profile_photo = null;
                $user->save();
                
                // If there are other photos, set the first one as the profile photo
                $nextPhoto = $user->photos()->where('id', '!=', $photo->id)->first();
                if ($nextPhoto) {
                    $user->profile_photo = $nextPhoto->photo_path;
                    $user->save();
                }
            }
            
            // If this was the primary photo, set another photo as primary
            if ($photo->is_primary) {
                $nextPhoto = $user->photos()->where('id', '!=', $photo->id)->first();
                if ($nextPhoto) {
                    $nextPhoto->is_primary = true;
                    $nextPhoto->save();
                }
            }
            
            // Delete the record
            $photo->delete();
            
            // Get all photos for the response
            $photos = $user->photos()->get()->map(function($photo) {
                return [
                    'id' => $photo->id,
                    'url' => $photo->url,
                    'is_primary' => $photo->is_primary,
                    'display_order' => $photo->display_order
                ];
            });
            
            return response()->json([
                'success' => true,
                'message' => 'Photo deleted successfully',
                'photos' => $photos
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error deleting photo: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'photo_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete photo: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Set a photo as primary
     */
    public function setPrimary($id)
    {
        try {
            $user = Auth::user();
            $photo = UserPhoto::where('id', $id)
                ->where('user_id', $user->id)
                ->first();
            
            if (!$photo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Photo not found'
                ], 404);
            }
            
            // Remove primary flag from all photos
            UserPhoto::where('user_id', $user->id)
                ->where('is_primary', true)
                ->update(['is_primary' => false]);
            
            // Set this photo as primary
            $photo->is_primary = true;
            $photo->save();
            
            // Also set as profile photo - only update profile_photo field
            $user->profile_photo = $photo->photo_path;
            $user->save();
            
            // Get all photos for the response
            $photos = $user->photos()->get()->map(function($photo) {
                return [
                    'id' => $photo->id,
                    'url' => $photo->url,
                    'is_primary' => $photo->is_primary,
                    'display_order' => $photo->display_order
                ];
            });
            
            return response()->json([
                'success' => true,
                'message' => 'Primary photo updated successfully',
                'photos' => $photos
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error setting primary photo: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'photo_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to set primary photo: ' . $e->getMessage()
            ], 500);
        }
    }
}

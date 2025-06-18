<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function dashboard()
    {
        // Get some basic stats for the dashboard
        $totalUsers = User::count();
        $pendingVerifications = User::whereHas('verification', function($query) {
            $query->where('status', 'pending');
        })->count();
        $recentUsers = User::with('verification')->latest()->take(5)->get();
        
        // Get reports stats
        $pendingReports = \App\Models\UserReport::where('reviewed', false)->count();
        
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'totalUsers' => $totalUsers,
                'pendingVerifications' => $pendingVerifications,
                'pendingReports' => $pendingReports,
            ],
            'recentUsers' => $recentUsers,
        ]);
    }
    
    /**
     * Display users list.
     */
    public function users()
    {
        $users = User::with('verification')->latest()->paginate(15);
        
        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
        ]);
    }
    
    /**
     * Display a specific user's profile details.
     */
    public function viewUser($userId)
    {
        $user = User::with([
            'verification',
            'photos',
            'appearance',
            'lifestyle', 
            'background',
            'about',
            'overview',
            'interests',
            'personality'
        ])->findOrFail($userId);

        // Transform photos to include full URLs
        if ($user->photos) {
            $user->photos->transform(function ($photo) {
                $photo->url = asset('storage/' . $photo->path);
                return $photo;
            });
        }

        return Inertia::render('Admin/Users/View', [
            'user' => $user,
        ]);
    }
    
    /**
     * Display verification requests.
     */
    public function verifications()
    {
        // Log current verification statuses for debugging
        $allVerifications = DB::table('verifications')->select('id', 'user_id', 'status')->get();
        \Log::info('All verifications:', ['verifications' => $allVerifications]);
        
        // Get all users with verified status directly from the database
        $approvedUsers = DB::table('users')
            ->join('verifications', 'users.id', '=', 'verifications.user_id')
            ->where('verifications.status', 'approved')
            ->select('users.*', 'verifications.created_at as verification_created_at', 
                    'verifications.verified_at', 'verifications.status', 'verifications.document_type')
            ->get();
            
        // Debug the approved verifications
        \Log::info('Approved users count (direct query): ' . $approvedUsers->count());
        
        // Also check for users with is_verified=true but verification status not approved
        // and fix them
        $usersToFix = DB::table('users')
            ->join('verifications', 'users.id', '=', 'verifications.user_id')
            ->where('users.is_verified', true)
            ->where('verifications.status', '!=', 'approved')
            ->select('users.id', 'users.name', 'users.email', 'verifications.status')
            ->get();
            
        foreach ($usersToFix as $user) {
            DB::table('verifications')
                ->where('user_id', $user->id)
                ->update([
                    'status' => 'approved',
                    'verified_at' => now()
                ]);
                
            \Log::info('Fixed user verification status:', [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'old_status' => $user->status,
                'new_status' => 'approved'
            ]);
        }
        
        // Refetch approved users after fixing
        if ($usersToFix->count() > 0) {
            $approvedUsers = DB::table('users')
                ->join('verifications', 'users.id', '=', 'verifications.user_id')
                ->where('verifications.status', 'approved')
                ->select('users.*', 'verifications.created_at as verification_created_at', 
                        'verifications.verified_at', 'verifications.status', 'verifications.document_type')
                ->get();
        }
        
        // Use DB facade for direct query to ensure we're getting the correct data
        $pendingUsers = DB::table('users')
            ->join('verifications', 'users.id', '=', 'verifications.user_id')
            ->where('verifications.status', 'pending')
            ->select('users.*', 'verifications.created_at as verification_created_at', 
                    'verifications.status', 'verifications.document_type')
            ->get();
            
        $rejectedUsers = DB::table('users')
            ->join('verifications', 'users.id', '=', 'verifications.user_id')
            ->where('verifications.status', 'rejected')
            ->select('users.*', 'verifications.created_at as verification_created_at', 
                    'verifications.status', 'verifications.rejection_reason', 'verifications.document_type')
            ->get();
        
        // Map the users to include verification data in the expected format
        $pendingData = [
            'data' => $pendingUsers->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'verification' => [
                        'created_at' => $user->verification_created_at,
                        'status' => $user->status,
                        'document_type' => $user->document_type
                    ]
                ];
            }),
            'total' => $pendingUsers->count()
        ];
        
        $approvedData = [
            'data' => $approvedUsers->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'verification' => [
                        'created_at' => $user->verification_created_at,
                        'verified_at' => $user->verified_at,
                        'status' => $user->status,
                        'document_type' => $user->document_type
                    ]
                ];
            }),
            'total' => $approvedUsers->count()
        ];
        
        $rejectedData = [
            'data' => $rejectedUsers->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'verification' => [
                        'created_at' => $user->verification_created_at,
                        'status' => $user->status,
                        'rejection_reason' => $user->rejection_reason,
                        'document_type' => $user->document_type
                    ]
                ];
            }),
            'total' => $rejectedUsers->count()
        ];
        
        return Inertia::render('Admin/Verifications/Index', [
            'pendingVerifications' => $pendingData,
            'approvedVerifications' => $approvedData,
            'rejectedVerifications' => $rejectedData,
        ]);
    }
    
    /**
     * Display verification details.
     */
    public function viewVerification($userId)
    {
        $user = User::with('verification')->findOrFail($userId);
        
        if (!$user->verification) {
            return redirect()->route('admin.verifications')->with('error', 'User has no verification record');
        }
        
        // Modify the verification object to include full URLs for images
        $verification = $user->verification;
        if ($verification->front_image) {
            $verification->front_image = asset('storage/' . $verification->front_image);
        }
        if ($verification->back_image) {
            $verification->back_image = asset('storage/' . $verification->back_image);
        }
        
        return Inertia::render('Admin/Verifications/View', [
            'user' => $user,
            'verification' => $verification,
        ]);
    }
    
    /**
     * Approve a verification.
     */
    public function approveVerification($userId)
    {
        $user = User::with('verification')->findOrFail($userId);
        
        if (!$user->verification) {
            return redirect()->route('admin.verifications')->with('error', 'User has no verification record');
        }
        
        // Debug before approving
        \Log::info('Before approval:', ['user_id' => $userId, 'verification' => $user->verification]);
        
        DB::transaction(function () use ($user) {
            // Update verification record
            $user->verification->update([
                'status' => 'approved',
                'verified_at' => Carbon::now(),
            ]);
            
            // Update user record
            $user->update([
                'is_verified' => true,
            ]);
        });
        
        // Debug after approving
        $refreshedUser = User::with('verification')->find($userId);
        \Log::info('After approval:', ['user_id' => $userId, 'verification' => $refreshedUser->verification]);
        
        return redirect()->route('admin.verifications')->with('success', 'Verification approved successfully');
    }
    
    /**
     * Reject a verification.
     */
    public function rejectVerification(Request $request, $userId)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);
        
        $user = User::with('verification')->findOrFail($userId);
        
        if (!$user->verification) {
            return redirect()->route('admin.verifications')->with('error', 'User has no verification record');
        }
        
        // Debug before rejecting
        \Log::info('Before rejection:', ['user_id' => $userId, 'verification' => $user->verification]);
        
        $user->verification->update([
            'status' => 'rejected',
            'rejection_reason' => $request->reason,
        ]);
        
        // Debug after rejecting
        $refreshedUser = User::with('verification')->find($userId);
        \Log::info('After rejection:', ['user_id' => $userId, 'verification' => $refreshedUser->verification]);
        
        return redirect()->route('admin.verifications')->with('success', 'Verification rejected successfully');
    }
} 
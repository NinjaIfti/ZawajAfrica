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
        
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'totalUsers' => $totalUsers,
                'pendingVerifications' => $pendingVerifications,
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
     * Display verification requests.
     */
    public function verifications()
    {
        // Log current verification statuses for debugging
        $allVerifications = DB::table('verifications')->select('id', 'user_id', 'status')->get();
        \Log::info('All verifications:', ['verifications' => $allVerifications]);
        
        $pendingVerifications = User::whereHas('verification', function($query) {
            $query->where('status', 'pending');
        })->with(['verification'])->get();
        
        $approvedVerifications = User::whereHas('verification', function($query) {
            $query->where('status', 'approved');
        })->with(['verification'])->get();
        
        // Debug the approved verifications
        \Log::info('Approved verifications count: ' . $approvedVerifications->count());
        
        $rejectedVerifications = User::whereHas('verification', function($query) {
            $query->where('status', 'rejected');
        })->with(['verification'])->get();
        
        // Convert to pagination-like structure for easier frontend usage
        $pendingData = [
            'data' => $pendingVerifications,
            'total' => count($pendingVerifications)
        ];
        
        $approvedData = [
            'data' => $approvedVerifications,
            'total' => count($approvedVerifications)
        ];
        
        $rejectedData = [
            'data' => $rejectedVerifications,
            'total' => count($rejectedVerifications)
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
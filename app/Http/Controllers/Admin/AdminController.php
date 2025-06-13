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
        $pendingVerifications = User::whereHas('verification', function($query) {
            $query->where('status', 'pending');
        })->with('verification')->paginate(15);
        
        return Inertia::render('Admin/Verifications/Index', [
            'pendingVerifications' => $pendingVerifications,
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
        
        return Inertia::render('Admin/Verifications/View', [
            'user' => $user,
            'verification' => $user->verification,
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
        
        $user->verification->update([
            'status' => 'rejected',
            'rejection_reason' => $request->reason,
        ]);
        
        return redirect()->route('admin.verifications')->with('success', 'Verification rejected successfully');
    }
} 
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

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
        
        // Get premium users count
        $premiumUsers = User::whereNotNull('subscription_plan')
            ->where('subscription_status', 'active')
            ->where(function($query) {
                $query->whereNull('subscription_expires_at')
                    ->orWhere('subscription_expires_at', '>', now());
            })
            ->count();

        // Tier-specific analytics
        $tierAnalytics = [
            'free_users' => User::where(function($query) {
                $query->whereNull('subscription_plan')
                      ->orWhere('subscription_status', '!=', 'active')
                      ->orWhere('subscription_expires_at', '<=', now());
            })->count(),
            'basic_users' => User::where('subscription_plan', 'basic')
                ->where('subscription_status', 'active')
                ->where(function($query) {
                    $query->whereNull('subscription_expires_at')
                          ->orWhere('subscription_expires_at', '>', now());
                })->count(),
            'gold_users' => User::where('subscription_plan', 'gold')
                ->where('subscription_status', 'active')
                ->where(function($query) {
                    $query->whereNull('subscription_expires_at')
                          ->orWhere('subscription_expires_at', '>', now());
                })->count(),
            'platinum_users' => User::where('subscription_plan', 'platinum')
                ->where('subscription_status', 'active')
                ->where(function($query) {
                    $query->whereNull('subscription_expires_at')
                          ->orWhere('subscription_expires_at', '>', now());
                })->count(),
        ];

        // Daily activity analytics
        $today = now()->format('Y-m-d');
        $activityAnalytics = [
            'daily_profile_views' => DB::table('user_daily_activities')
                ->where('activity', 'profile_views')
                ->where('date', $today)
                ->sum('count'),
            'daily_messages' => DB::table('user_daily_activities')
                ->where('activity', 'messages_sent')
                ->where('date', $today)
                ->sum('count'),
            'daily_likes' => DB::table('user_daily_activities')
                ->where('activity', 'likes_sent')
                ->where('date', $today)
                ->sum('count'),
            'daily_matches' => DB::table('user_daily_activities')
                ->where('activity', 'matches_created')
                ->where('date', $today)
                ->sum('count'),
        ];

        // Conversion analytics (users hitting limits)
        $limitAnalytics = [
            'free_users_at_view_limit' => DB::table('user_daily_activities')
                ->join('users', 'user_daily_activities.user_id', '=', 'users.id')
                ->where('user_daily_activities.activity', 'profile_views')
                ->where('user_daily_activities.date', $today)
                ->where('user_daily_activities.count', '>=', 63) // 90% of 70 limit
                ->where(function($query) {
                    $query->whereNull('users.subscription_plan')
                          ->orWhere('users.subscription_status', '!=', 'active')
                          ->orWhere('users.subscription_expires_at', '<=', now());
                })
                ->count(),
            'basic_users_at_message_limit' => DB::table('user_daily_activities')
                ->join('users', 'user_daily_activities.user_id', '=', 'users.id')
                ->where('user_daily_activities.activity', 'messages_sent')
                ->where('user_daily_activities.date', $today)
                ->where('user_daily_activities.count', '>=', 25) // ~80% of 30 limit
                ->where('users.subscription_plan', 'basic')
                ->where('users.subscription_status', 'active')
                ->count(),
        ];
        
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'totalUsers' => $totalUsers,
                'pendingVerifications' => $pendingVerifications,
                'pendingReports' => $pendingReports,
                'premiumUsers' => $premiumUsers,
            ],
            'recentUsers' => $recentUsers,
            'tier_analytics' => $tierAnalytics,
            'activity_analytics' => $activityAnalytics,
            'limit_analytics' => $limitAnalytics,
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
        
        // Send approval notification using Zoho Mail
        try {
            $user->notify(new \App\Notifications\VerificationApproved());
            \Log::info('Verification approval email sent successfully', [
                'user_id' => $userId,
                'user_email' => $user->email
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send verification approval email', [
                'user_id' => $userId,
                'user_email' => $user->email,
                'error' => $e->getMessage()
            ]);
            // Don't fail the approval process if email fails
        }
        
        // Debug after approving
        $refreshedUser = User::with('verification')->find($userId);
        \Log::info('After approval:', ['user_id' => $userId, 'verification' => $refreshedUser->verification]);
        
        return redirect()->route('admin.verifications')->with('success', 'Verification approved successfully and notification sent');
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
        
        $rejectionReason = $request->reason;
        
        $user->verification->update([
            'status' => 'rejected',
            'rejection_reason' => $rejectionReason,
        ]);

        // Ensure user is_verified remains false but user can still access the site
        $user->update([
            'is_verified' => false,
        ]);
        
        // Send rejection notification using Zoho Mail
        try {
            $user->notify(new \App\Notifications\VerificationRejected($rejectionReason));
            \Log::info('Verification rejection email sent successfully', [
                'user_id' => $userId,
                'user_email' => $user->email,
                'reason' => $rejectionReason
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send verification rejection email', [
                'user_id' => $userId,
                'user_email' => $user->email,
                'error' => $e->getMessage()
            ]);
            // Don't fail the rejection process if email fails
        }
        
        // Debug after rejecting
        $refreshedUser = User::with('verification')->find($userId);
        \Log::info('After rejection:', ['user_id' => $userId, 'verification' => $refreshedUser->verification]);
        
        return redirect()->route('admin.verifications')->with('success', 'Verification rejected and notification sent');
    }

    /**
     * Display subscriptions management page.
     */
    public function subscriptions()
    {
        $subscriptions = User::whereNotNull('subscription_plan')
            ->with(['verification'])
            ->latest('subscription_expires_at')
            ->paginate(15);

        $stats = [
            'total' => User::whereNotNull('subscription_plan')->count(),
            'active' => User::where('subscription_status', 'active')
                ->where(function($query) {
                    $query->whereNull('subscription_expires_at')
                        ->orWhere('subscription_expires_at', '>', now());
                })
                ->count(),
            'expired' => User::where('subscription_status', 'active')
                ->where('subscription_expires_at', '<=', now())
                ->count(),
            'cancelled' => User::where('subscription_status', 'cancelled')->count(),
        ];

        return Inertia::render('Admin/Subscriptions/Index', [
            'subscriptions' => $subscriptions,
            'stats' => $stats,
        ]);
    }

    /**
     * Get premium users for modal display.
     */
    public function getPremiumUsers()
    {
        $premiumUsers = User::whereNotNull('subscription_plan')
            ->where('subscription_status', 'active')
            ->where(function($query) {
                $query->whereNull('subscription_expires_at')
                    ->orWhere('subscription_expires_at', '>', now());
            })
            ->with(['verification'])
            ->latest('subscription_expires_at')
            ->take(10)
            ->get();

        return response()->json([
            'users' => $premiumUsers,
            'total' => User::whereNotNull('subscription_plan')
                ->where('subscription_status', 'active')
                ->where(function($query) {
                    $query->whereNull('subscription_expires_at')
                        ->orWhere('subscription_expires_at', '>', now());
                })
                ->count(),
        ]);
    }

    /**
     * Delete a user and all related data
     */
    public function deleteUser($userId)
    {
        try {
            DB::beginTransaction();
            
            $user = User::findOrFail($userId);
            
            \Log::info('Starting user deletion process', [
                'user_id' => $userId,
                'user_name' => $user->name,
                'user_email' => $user->email
            ]);

            // Delete user photos from storage and database
            if ($user->photos) {
                foreach ($user->photos as $photo) {
                    if ($photo->path && \Storage::disk('public')->exists($photo->path)) {
                        \Storage::disk('public')->delete($photo->path);
                        \Log::info('Deleted photo file', ['path' => $photo->path]);
                    }
                }
                $user->photos()->delete();
                \Log::info('Deleted user photos from database');
            }

            // Delete profile photo if exists
            if ($user->profile_photo && \Storage::disk('public')->exists($user->profile_photo)) {
                \Storage::disk('public')->delete($user->profile_photo);
                \Log::info('Deleted profile photo', ['path' => $user->profile_photo]);
            }

            // Delete verification documents from storage
            if ($user->verification) {
                if ($user->verification->front_image && \Storage::disk('public')->exists($user->verification->front_image)) {
                    \Storage::disk('public')->delete($user->verification->front_image);
                    \Log::info('Deleted verification front image', ['path' => $user->verification->front_image]);
                }
                if ($user->verification->back_image && \Storage::disk('public')->exists($user->verification->back_image)) {
                    \Storage::disk('public')->delete($user->verification->back_image);
                    \Log::info('Deleted verification back image', ['path' => $user->verification->back_image]);
                }
                $user->verification()->delete();
                \Log::info('Deleted verification record');
            }

            // Delete all user-related data
            $deletedCounts = [];

            // Delete user profile data
            if ($user->appearance) {
                $user->appearance()->delete();
                $deletedCounts['appearance'] = 1;
            }
            
            if ($user->lifestyle) {
                $user->lifestyle()->delete();
                $deletedCounts['lifestyle'] = 1;
            }
            
            if ($user->background) {
                $user->background()->delete();
                $deletedCounts['background'] = 1;
            }
            
            if ($user->about) {
                $user->about()->delete();
                $deletedCounts['about'] = 1;
            }
            
            if ($user->overview) {
                $user->overview()->delete();
                $deletedCounts['overview'] = 1;
            }

            // Delete interests and personality data
            $deletedCounts['interests'] = $user->interests()->count();
            $user->interests()->delete();
            
            $deletedCounts['personality'] = $user->personality()->count();
            $user->personality()->delete();

            // Delete messages (both sent and received)
            $deletedCounts['sent_messages'] = $user->sentMessages()->count();
            $deletedCounts['received_messages'] = $user->receivedMessages()->count();
            $user->sentMessages()->delete();
            $user->receivedMessages()->delete();

            // Delete therapist bookings
            $deletedCounts['therapist_bookings'] = $user->therapistBookings()->count();
            $user->therapistBookings()->delete();

            // Delete user likes (given and received)
            $givenLikes = \App\Models\UserLike::where('user_id', $userId)->count();
            $receivedLikes = \App\Models\UserLike::where('liked_user_id', $userId)->count();
            \App\Models\UserLike::where('user_id', $userId)->delete();
            \App\Models\UserLike::where('liked_user_id', $userId)->delete();
            $deletedCounts['likes'] = $givenLikes + $receivedLikes;

            // Delete user matches
            $matches1 = \App\Models\UserMatch::where('user1_id', $userId)->count();
            $matches2 = \App\Models\UserMatch::where('user2_id', $userId)->count();
            \App\Models\UserMatch::where('user1_id', $userId)->delete();
            \App\Models\UserMatch::where('user2_id', $userId)->delete();
            $deletedCounts['matches'] = $matches1 + $matches2;

            // Delete user reports (made by and against user)
            $deletedCounts['reports_made'] = $user->reportsMade()->count();
            $deletedCounts['reports_received'] = $user->reportsReceived()->count();
            $user->reportsMade()->delete();
            $user->reportsReceived()->delete();

            // Delete notifications
            $deletedCounts['notifications'] = $user->notifications()->count();
            $user->notifications()->delete();

            // Delete failed notifications
            $failedNotifications = DB::table('failed_notifications')->where('user_id', $userId)->count();
            DB::table('failed_notifications')->where('user_id', $userId)->delete();
            $deletedCounts['failed_notifications'] = $failedNotifications;

            // Finally delete the user
            $user->delete();

            DB::commit();

            \Log::info('User deletion completed successfully', [
                'user_id' => $userId,
                'deleted_counts' => $deletedCounts
            ]);

            return redirect()->route('admin.users')->with('success', 
                "User and all related data deleted successfully. Deleted: " . 
                collect($deletedCounts)->map(function($count, $type) {
                    return "$count $type";
                })->implode(', ')
            );

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('User deletion failed', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'Failed to delete user: ' . $e->getMessage());
        }
    }

    /**
     * Display admin settings page.
     */
    public function settings()
    {
        return Inertia::render('Admin/Settings', [
            'admin' => auth()->user()->only(['name', 'email']),
        ]);
    }

    /**
     * Update admin password.
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Password updated successfully.');
    }
} 
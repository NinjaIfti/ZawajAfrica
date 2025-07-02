<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Models\ChatbotConversation;
use App\Services\AIEmailService;
use App\Services\OpenAIService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Models\UserReport;
use App\Models\Verification;
use App\Models\TherapistBooking;
use App\Models\Profile;
use Illuminate\Support\Facades\Validator;
use App\Services\UserTierService;
use App\Services\NotificationService;

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

        $allVerifications = DB::table('verifications')->select('id', 'user_id', 'status')->get();
        \Log::info('All verifications:', ['verifications' => $allVerifications]);
        
        // Get all users with verified status directly from the database
        $approvedUsers = DB::table('users')
            ->join('verifications', 'users.id', '=', 'verifications.user_id')
            ->where('verifications.status', 'approved')
            ->select('users.*', 'verifications.created_at as verification_created_at', 
                    'verifications.verified_at', 'verifications.status', 'verifications.document_type')
            ->get();
            

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
        
        // Non-verified users: no verification record or status is null/empty
        $nonVerifiedUsers = User::leftJoin('verifications', 'users.id', '=', 'verifications.user_id')
            ->where(function($query) {
                $query->whereNull('verifications.status')
                      ->orWhere('verifications.status', '')
                      ->orWhere('verifications.status', 'nonverified');
            })
            ->where('users.is_verified', false)
            ->select('users.*')
            ->get();

        $nonVerifiedData = [
            'data' => $nonVerifiedUsers->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ];
            }),
            'total' => $nonVerifiedUsers->count()
        ];
        
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
            'nonVerifiedUsers' => $nonVerifiedData,
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
        
        
        $refreshedUser = User::with('verification')->find($userId);
        \Log::info('After rejection:', ['user_id' => $userId, 'verification' => $refreshedUser->verification]);
        
        return redirect()->route('admin.verifications')->with('success', 'Verification rejected and notification sent');
    }

    /**
     * Display subscriptions management page.
     */
    public function subscriptions(Request $request)
    {
        $status = $request->input('status', 'all'); // all, paid, nonpaid
        $plan = $request->input('plan', 'all'); // all, basic, gold, platinum, none

        $query = User::query()->with(['verification']);

        // Filter by paid/nonpaid
        if ($status === 'paid') {
            $query->whereNotNull('subscription_plan');
        } elseif ($status === 'nonpaid') {
            $query->whereNull('subscription_plan');
        }

        // Filter by plan
        if (in_array($plan, ['basic', 'gold', 'platinum'])) {
            $query->where('subscription_plan', $plan);
        } elseif ($plan === 'none') {
            $query->whereNull('subscription_plan');
        }

        $subscriptions = $query->latest('subscription_expires_at')->paginate(15);

        $stats = [
            'total' => User::count(),
            'paid' => User::whereNotNull('subscription_plan')->count(),
            'nonpaid' => User::whereNull('subscription_plan')->count(),
            'active' => User::where('subscription_status', 'active')
                ->where(function($query) {
                    $query->whereNull('subscription_expires_at')
                        ->orWhere('subscription_expires_at', '>', now());
                })
                ->count(),
            'expired' => User::where(function($query) {
                    $query->where('subscription_status', 'expired')
                        ->orWhere(function($subQuery) {
                            $subQuery->where('subscription_status', 'active')
                                ->whereNotNull('subscription_expires_at')
                                ->where('subscription_expires_at', '<=', now());
                        });
                })
                ->count(),
            'cancelled' => User::where('subscription_status', 'cancelled')->count(),
        ];

        return Inertia::render('Admin/Subscriptions/Index', [
            'subscriptions' => $subscriptions,
            'stats' => $stats,
            'filters' => [
                'status' => $status,
                'plan' => $plan,
            ],
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
     * Extend user subscription by 30 days
     */
    public function extendSubscription(User $user)
    {
        try {
            if (!$user->subscription_plan) {
                return response()->json(['message' => 'User has no subscription plan to extend'], 400);
            }

            // If subscription is expired or about to expire, extend from now
            // If still active, extend from current expiry date
            $currentExpiry = $user->subscription_expires_at;
            if (!$currentExpiry || $currentExpiry->isPast()) {
                $newExpiry = now()->addDays(30);
            } else {
                $newExpiry = $currentExpiry->addDays(30);
            }

            $user->update([
                'subscription_status' => 'active',
                'subscription_expires_at' => $newExpiry
            ]);

            \Log::info('Admin extended subscription', [
                'admin_id' => auth()->id(),
                'user_id' => $user->id,
                'old_expiry' => $currentExpiry,
                'new_expiry' => $newExpiry
            ]);

            return response()->json(['message' => 'Subscription extended successfully']);
        } catch (\Exception $e) {
            \Log::error('Failed to extend subscription', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['message' => 'Failed to extend subscription'], 500);
        }
    }

    /**
     * Cancel user subscription
     */
    public function cancelSubscription(User $user)
    {
        try {
            $user->update([
                'subscription_status' => 'cancelled'
            ]);

            \Log::info('Admin cancelled subscription', [
                'admin_id' => auth()->id(),
                'user_id' => $user->id,
                'plan' => $user->subscription_plan
            ]);

            return response()->json(['message' => 'Subscription cancelled successfully']);
        } catch (\Exception $e) {
            \Log::error('Failed to cancel subscription', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['message' => 'Failed to cancel subscription'], 500);
        }
    }

    /**
     * Reactivate user subscription
     */
    public function reactivateSubscription(User $user)
    {
        try {
            $newExpiry = now()->addDays(30);

            $user->update([
                'subscription_status' => 'active',
                'subscription_expires_at' => $newExpiry
            ]);

            \Log::info('Admin reactivated subscription', [
                'admin_id' => auth()->id(),
                'user_id' => $user->id,
                'plan' => $user->subscription_plan,
                'new_expiry' => $newExpiry
            ]);

            return response()->json(['message' => 'Subscription reactivated successfully']);
        } catch (\Exception $e) {
            \Log::error('Failed to reactivate subscription', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['message' => 'Failed to reactivate subscription'], 500);
        }
    }

    /**
     * Gift subscription to user with specified plan
     */
    public function giftSubscription(Request $request, User $user)
    {
        try {
            $request->validate([
                'plan' => 'required|string|in:basic,gold,platinum'
            ]);

            $plan = $request->plan;
            // Capitalize plan name to match frontend expectations
            $capitalizedPlan = ucfirst($plan); // 'basic' -> 'Basic', 'gold' -> 'Gold', 'platinum' -> 'Platinum'
            $expiresAt = now()->addMonth(); // 1 month automatic expiration

            $user->update([
                'subscription_plan' => $capitalizedPlan,
                'subscription_status' => 'active',
                'subscription_expires_at' => $expiresAt
            ]);

            \Log::info('Admin gifted subscription', [
                'admin_id' => auth()->id(),
                'user_id' => $user->id,
                'plan' => $capitalizedPlan,
                'expires_at' => $expiresAt
            ]);

            // Optional: Notify the user that they've been gifted a subscription
            try {
                $user->notify(new \App\Notifications\SubscriptionPurchased(
                    $capitalizedPlan,
                    0, // Amount is 0 since it's a gift
                    'admin_gift_' . time(), // Reference
                    $expiresAt->toDateTime()
                ));
            } catch (\Exception $e) {
                \Log::warning('Failed to send subscription gift notification', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
                // Continue even if notification fails
            }

            return response()->json([
                'message' => "Successfully gifted {$capitalizedPlan} plan to {$user->name}",
                'expires_at' => $expiresAt->format('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to gift subscription', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['message' => 'Failed to gift subscription: ' . $e->getMessage()], 500);
        }
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

    /**
     * Update user verification status (verified, pending, non-verified)
     */
    public function updateUserVerification(Request $request, User $user)
    {
        $request->validate([
            'status' => 'required|in:verified,pending,nonverified'
        ]);

        $status = $request->input('status');

        // Ensure verification record exists
        if (!$user->verification) {
            $user->verification()->create([
                'status' => $status === 'verified' ? 'approved' : ($status === 'pending' ? 'pending' : 'rejected'),
                'verified_at' => $status === 'verified' ? now() : null,
            ]);
        } else {
            if ($status === 'verified') {
                $user->verification->status = 'approved';
                $user->verification->verified_at = now();
                $user->verification->save();
            } elseif ($status === 'pending') {
                $user->verification->status = 'pending';
                $user->verification->verified_at = null;
                $user->verification->save();
            } else { // nonverified
                $user->verification->status = 'rejected';
                $user->verification->verified_at = null;
                $user->verification->save();
            }
        }

        $user->is_verified = $status === 'verified';
        $user->save();

        \Log::info('Admin updated user verification status', [
            'admin_id' => auth()->id(),
            'user_id' => $user->id,
            'status' => $status
        ]);

        return response()->json(['message' => 'Verification status updated successfully.']);
    }

    /**
     * AI-powered admin tools and insights
     */
    public function aiInsights()
    {
        $chatbotStats = ChatbotConversation::getStatistics();
        
        $userEngagementData = [
            'most_active_chatbot_users' => ChatbotConversation::select('user_id', DB::raw('COUNT(*) as message_count'))
                ->with('user:id,name,email')
                ->groupBy('user_id')
                ->orderByDesc('message_count')
                ->limit(10)
                ->get(),
            'recent_conversations' => ChatbotConversation::with('user:id,name')
                ->latest()
                ->limit(20)
                ->get(),
            'daily_ai_usage' => ChatbotConversation::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->where('created_at', '>=', now()->subDays(30))
                ->groupBy('date')
                ->orderBy('date')
                ->get()
        ];

        return Inertia::render('Admin/AIInsights', [
            'chatbot_stats' => $chatbotStats,
            'user_engagement' => $userEngagementData
        ]);
    }

    /**
     * Generate AI broadcast email
     */
    public function generateBroadcast(Request $request)
    {
        $request->validate([
            'message_type' => 'required|string|in:announcement,promotion,update,newsletter',
            'target_audience' => 'required|string|in:all,premium,basic,free',
            'topic' => 'required|string|max:200',
            'tone' => 'required|string|in:formal,friendly,exciting,professional'
        ]);

        try {
            // Increase execution time for broadcast generation
            set_time_limit(120); // 2 minutes
            
            $openAIService = app(OpenAIService::class);

            $prompt = $this->buildBroadcastPrompt(
                $request->message_type,
                $request->target_audience,
                $request->topic,
                $request->tone
            );

            $messages = [
                ['role' => 'user', 'content' => $prompt]
            ];

            $response = $openAIService->chat($messages);

            if ($response['success']) {
                // Parse AI response to extract subject and body
                $content = $response['message'];
                $parts = explode("\n\n", $content, 2);
                
                $subject = str_replace('Subject: ', '', $parts[0]);
                $body = $parts[1] ?? $content;

                return response()->json([
                    'success' => true,
                    'subject' => $subject,
                    'body' => $body,
                    'preview' => substr(strip_tags($body), 0, 150) . '...'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => $response['error']
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('AI broadcast generation failed', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to generate broadcast message'
            ], 500);
        }
    }

    /**
     * Generate user insights using AI
     */
    public function generateUserInsights()
    {
        try {
            // Increase execution time for insights generation
            set_time_limit(180); // 3 minutes
            
            $openAIService = app(OpenAIService::class);

            // Gather data for insights
            $analyticsData = $this->gatherUserAnalytics();

            $prompt = $this->buildInsightsPrompt($analyticsData);

            $messages = [
                ['role' => 'user', 'content' => $prompt]
            ];

            $response = $openAIService->chat($messages);

            if ($response['success']) {
                return response()->json([
                    'success' => true,
                    'insights' => $response['message'],
                    'raw_data' => $analyticsData
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => $response['error']
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('AI insights generation failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to generate user insights'
            ], 500);
        }
    }

    /**
     * Send broadcast email to users
     */
    public function sendBroadcast(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:200',
            'body' => 'required|string|max:5000',
            'target_audience' => 'required|string|in:all,premium,basic,free',
            'is_edited' => 'boolean'
        ]);

        // Prevent multiple simultaneous broadcasts
        $lock = \Cache::lock('broadcast_email_lock', 1800); // 30 minutes
        if (!$lock->get()) {
            return response()->json([
                'success' => false,
                'error' => 'A broadcast is already in progress. Please wait until it finishes.'
            ], 429);
        }

        try {
            // Set execution time limit for this operation
            set_time_limit(300); // 5 minutes
            
            // Get users based on target audience
            $users = $this->getUsersByAudience($request->target_audience);
            
            if ($users->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'error' => 'No users found for the selected audience'
                ], 400);
            }

            // Broadcasting is not available - use Zoho opt-in form instead
            return response()->json([
                'success' => false,
                'error' => 'Broadcasting is not available. Please use the Zoho opt-in form for email campaigns.'
            ], 503);

        } catch (\Exception $e) {
            Log::error('Broadcast email campaign failed', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to send broadcast emails: ' . $e->getMessage()
            ], 500);
        } finally {
            $lock->release();
        }
    }

    /**
     * Save broadcast email draft
     */
    public function saveBroadcastDraft(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:200',
            'body' => 'required|string|max:5000',
            'target_audience' => 'required|string|in:all,premium,basic,free',
            'message_type' => 'required|string|in:announcement,promotion,update,newsletter',
            'tone' => 'required|string|in:formal,friendly,exciting,professional',
            'topic' => 'required|string|max:200'
        ]);

        try {
            // Save draft to cache with admin ID as key
            $draftKey = 'broadcast_draft_' . auth()->id();
            $draftData = [
                'subject' => $request->subject,
                'body' => $request->body,
                'target_audience' => $request->target_audience,
                'message_type' => $request->message_type,
                'tone' => $request->tone,
                'topic' => $request->topic,
                'saved_at' => now()->toISOString(),
                'admin_id' => auth()->id()
            ];

            \Cache::put($draftKey, $draftData, 7 * 24 * 60); // Save for 7 days

            Log::info('Broadcast draft saved', [
                'admin_id' => auth()->id(),
                'subject' => $request->subject,
                'target_audience' => $request->target_audience
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Draft saved successfully',
                'saved_at' => $draftData['saved_at']
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to save broadcast draft', [
                'admin_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to save draft'
            ], 500);
        }
    }

    /**
     * Load broadcast email draft
     */
    public function loadBroadcastDraft()
    {
        try {
            $draftKey = 'broadcast_draft_' . auth()->id();
            $draftData = \Cache::get($draftKey);

            if (!$draftData) {
                return response()->json([
                    'success' => false,
                    'message' => 'No draft found'
                ]);
            }

            return response()->json([
                'success' => true,
                'draft' => $draftData
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to load broadcast draft', [
                'admin_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to load draft'
            ], 500);
        }
    }

    /**
     * Delete broadcast email draft
     */
    public function deleteBroadcastDraft()
    {
        try {
            $draftKey = 'broadcast_draft_' . auth()->id();
            \Cache::forget($draftKey);

            return response()->json([
                'success' => true,
                'message' => 'Draft deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to delete broadcast draft', [
                'admin_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to delete draft'
            ], 500);
        }
    }

    /**
     * Get users based on target audience
     */
    private function getUsersByAudience(string $audience)
    {
        $query = User::whereNotNull('email')->where('email', '!=', '');

        switch ($audience) {
            case 'premium':
                return $query->whereNotNull('subscription_plan')
                            ->where('subscription_status', 'active')
                            ->whereIn('subscription_plan', ['premium_monthly', 'premium_yearly'])
                            ->get(['id', 'name', 'email']);
            
            case 'basic':
                return $query->whereNotNull('subscription_plan')
                            ->where('subscription_status', 'active')
                            ->whereIn('subscription_plan', ['basic_monthly', 'basic_yearly'])
                            ->get(['id', 'name', 'email']);
            
            case 'free':
                return $query->where(function($q) {
                    $q->whereNull('subscription_plan')
                      ->orWhere('subscription_status', '!=', 'active');
                })->get(['id', 'name', 'email']);
            
            case 'all':
            default:
                return $query->get(['id', 'name', 'email']);
        }
    }

    /**
     * Build broadcast prompt for AI
     */
    private function buildBroadcastPrompt(string $messageType, string $audience, string $topic, string $tone): string
    {
        return "Generate a professional email broadcast for ZawajAfrica platform.

Details:
- Message Type: {$messageType}
- Target Audience: {$audience} users
- Topic: {$topic}
- Tone: {$tone}

Requirements:
1. Start with 'Subject: ' followed by compelling subject line
2. Write for African Muslim audience
3. Maintain Islamic values and cultural sensitivity
4. Include call-to-action appropriate for the message type
5. Keep under 400 words
6. End with appropriate Islamic greeting

Format:
Subject: [subject line]

[email body]";
    }

    /**
     * Gather user analytics data
     */
    private function gatherUserAnalytics(): array
    {
        $last30Days = now()->subDays(30);
        
        return [
            'user_growth' => [
                'new_users_last_30_days' => User::where('created_at', '>=', $last30Days)->count(),
                'total_users' => User::count(),
                'verified_users' => User::where('is_verified', true)->count(),
                'premium_users' => User::whereNotNull('subscription_plan')
                    ->where('subscription_status', 'active')->count()
            ],
            'engagement' => [
                'active_users_last_7_days' => User::where('updated_at', '>=', now()->subDays(7))->count(),
                'chatbot_conversations_last_30_days' => ChatbotConversation::where('created_at', '>=', $last30Days)->count(),
                'total_matches_created' => \App\Models\UserMatch::count(),
                'total_messages_sent' => \App\Models\Message::count()
            ],
            'conversion' => [
                'free_to_premium_rate' => $this->calculateConversionRate(),
                'profile_completion_rate' => $this->calculateProfileCompletionRate()
            ]
        ];
    }

    /**
     * Build insights prompt for AI
     */
    private function buildInsightsPrompt(array $data): string
    {
        $dataJson = json_encode($data, JSON_PRETTY_PRINT);

        return "Analyze the following ZawajAfrica platform data and provide actionable insights.

Platform Data:
{$dataJson}

Please provide:
1. Key trends and patterns
2. Areas of concern or opportunity
3. Specific recommendations for:
   - User acquisition
   - User engagement
   - Premium conversion
   - Platform improvements
4. Predictions for next month
5. Priority action items

Keep it professional, data-driven, and actionable for platform administrators.";
    }

    /**
     * Calculate conversion rate
     */
    private function calculateConversionRate(): float
    {
        $totalUsers = User::count();
        $premiumUsers = User::whereNotNull('subscription_plan')
            ->where('subscription_status', 'active')->count();
        
        return $totalUsers > 0 ? round(($premiumUsers / $totalUsers) * 100, 2) : 0;
    }

    /**
     * Calculate profile completion rate
     */
    private function calculateProfileCompletionRate(): float
    {
        $usersWithProfiles = User::whereHas('profile')->count();
        $totalUsers = User::count();
        
        return $totalUsers > 0 ? round(($usersWithProfiles / $totalUsers) * 100, 2) : 0;
    }

    /**
     * Handle AI user insights queries
     */
    public function handleUserInsights(Request $request)
    {
        $request->validate([
            'query' => 'required|string|max:500',
            'conversation_history' => 'array|max:20'
        ]);

        try {
            set_time_limit(120); // 2 minutes for complex queries
            
            $query = $request->input('query');
            $conversationHistory = $request->input('conversation_history', []);
            
            // Analyze the query to determine what data to fetch
            $queryAnalysis = $this->analyzeUserQuery($query);
            
            // Fetch relevant data based on query analysis
            $userData = $this->fetchUserDataForQuery($queryAnalysis);
            
            // Generate AI response with the data
            $aiResponse = $this->generateUserInsightResponse($query, $userData, $conversationHistory);
            
            // Defensive check for 'message' key (OpenAI service returns response in 'message' field)
            if (isset($aiResponse['success']) && $aiResponse['success'] && isset($aiResponse['message'])) {
                return response()->json([
                    'success' => true,
                    'response' => $aiResponse['message'],
                    'data' => $userData,
                    'query_type' => $queryAnalysis['type']
                ]);
            } else {
                \Log::error('AI user insights missing message key', [
                    'admin_id' => auth()->id(),
                    'query' => $query,
                    'ai_response' => $aiResponse,
                ]);
                return response()->json([
                    'success' => false,
                    'error' => $aiResponse['error'] ?? 'AI service did not return a valid response. Please try again later.'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('AI user insights query failed', [
                'admin_id' => auth()->id(),
                'query' => $query,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to process user insights query'
            ], 500);
        }
    }

    /**
     * User Insights page with stats
     */
    public function userInsightsPage()
    {
        $today = now()->format('Y-m-d');
        
        // Active users today
        $activeUsersToday = DB::table('user_daily_activities')
            ->where('date', $today)
            ->distinct('user_id')
            ->count('user_id');
        
        // Platform activity today
        $platformActivityToday = DB::table('user_daily_activities')
            ->where('date', $today)
            ->sum('count');
        
        // Premium users
        $premiumUsers = User::whereNotNull('subscription_plan')
            ->where('subscription_status', 'active')
            ->count();
        
        // Users at limits today
        $usersAtLimits = DB::table('user_daily_activities')
            ->join('users', 'user_daily_activities.user_id', '=', 'users.id')
            ->where('user_daily_activities.date', $today)
            ->where(function($query) {
                $query->where('user_daily_activities.activity', 'profile_views')
                      ->where('user_daily_activities.count', '>=', 63)
                      ->orWhere(function($q) {
                          $q->where('user_daily_activities.activity', 'messages_sent')
                            ->where('user_daily_activities.count', '>=', 25);
                      });
            })
            ->distinct('users.id')
            ->count('users.id');

        return Inertia::render('Admin/UserInsights', [
            'stats' => [
                'active_users_today' => $activeUsersToday,
                'platform_activity_today' => $platformActivityToday,
                'premium_users' => $premiumUsers,
                'users_at_limits' => $usersAtLimits
            ]
        ]);
    }

    /**
     * Analyze user query to determine data requirements
     */
    private function analyzeUserQuery(string $query): array
    {
        $query = strtolower($query);
        
        // Check for specific user queries
        if (preg_match('/user(?:\s+id)?\s+(\d+)|id\s*:?\s*(\d+)/', $query, $matches)) {
            return [
                'type' => 'specific_user',
                'user_id' => $matches[1] ?? $matches[2],
                'include_activities' => true,
                'include_profile' => true
            ];
        }
        
        // Check for user name queries
        if (preg_match('/user\s+([a-zA-Z\s]+)|for\s+([a-zA-Z\s]+)/', $query, $matches)) {
            $name = trim($matches[1] ?? $matches[2]);
            if (strlen($name) > 2) {
                return [
                    'type' => 'user_by_name',
                    'user_name' => $name,
                    'include_activities' => true,
                    'include_profile' => true
                ];
            }
        }
        
        // Check for activity-based queries
        if (str_contains($query, 'active') || str_contains($query, 'activity') || str_contains($query, 'engagement')) {
            return [
                'type' => 'activity_analysis',
                'time_range' => $this->extractTimeRange($query),
                'include_top_users' => true,
                'limit' => $this->extractLimit($query)
            ];
        }
        
        // Check for limit-related queries
        if (str_contains($query, 'limit') || str_contains($query, 'quota') || str_contains($query, 'restriction')) {
            return [
                'type' => 'user_limits',
                'include_at_limit' => true,
                'time_range' => 'today'
            ];
        }
        
        // Check for premium/tier queries
        if (str_contains($query, 'premium') || str_contains($query, 'tier') || str_contains($query, 'subscription')) {
            return [
                'type' => 'tier_analysis',
                'include_conversion' => true,
                'time_range' => $this->extractTimeRange($query)
            ];
        }
        
        // Check for upgrade suggestions
        if (str_contains($query, 'upgrade') || str_contains($query, 'convert') || str_contains($query, 'upsell')) {
            return [
                'type' => 'upgrade_candidates',
                'include_suggestions' => true,
                'limit' => $this->extractLimit($query)
            ];
        }
        
        // Default general analytics
        return [
            'type' => 'general_analytics',
            'time_range' => 'today',
            'include_overview' => true
        ];
    }

    /**
     * Extract time range from query
     */
    private function extractTimeRange(string $query): string
    {
        if (str_contains($query, 'today')) return 'today';
        if (str_contains($query, 'yesterday')) return 'yesterday';
        if (str_contains($query, 'week')) return 'week';
        if (str_contains($query, 'month')) return 'month';
        if (str_contains($query, 'year')) return 'year';
        
        return 'today'; // default
    }

    /**
     * Extract limit from query
     */
    private function extractLimit(string $query): int
    {
        if (preg_match('/top\s+(\d+)|(\d+)\s+most/', $query, $matches)) {
            return min((int)($matches[1] ?? $matches[2]), 50); // Max 50
        }
        
        return 10; // default
    }

    /**
     * Fetch user data based on query analysis
     */
    private function fetchUserDataForQuery(array $analysis): array
    {
        $data = [];
        
        switch ($analysis['type']) {
            case 'specific_user':
                $data = $this->fetchSpecificUserData($analysis['user_id']);
                break;
                
            case 'user_by_name':
                $data = $this->fetchUserByName($analysis['user_name']);
                break;
                
            case 'activity_analysis':
                $data = $this->fetchActivityAnalysis($analysis);
                break;
                
            case 'user_limits':
                $data = $this->fetchUsersAtLimits();
                break;
                
            case 'tier_analysis':
                $data = $this->fetchTierAnalysis($analysis['time_range']);
                break;
                
            case 'upgrade_candidates':
                $data = $this->fetchUpgradeCandidates($analysis['limit']);
                break;
                
            case 'general_analytics':
            default:
                $data = $this->fetchGeneralAnalytics($analysis['time_range']);
                break;
        }
        
        // Always include therapist information for context
        $data['therapist_info'] = $this->fetchTherapistInfo();
        
        return $data;
    }

    /**
     * Fetch therapist information for AI context
     */
    private function fetchTherapistInfo(): array
    {
        $therapists = \App\Models\Therapist::with(['bookings' => function($query) {
            $query->where('created_at', '>=', now()->subDays(30));
        }])->get();
        
        $therapistStats = [
            'total_therapists' => $therapists->count(),
            'active_therapists' => $therapists->where('is_active', true)->count(),
            'total_bookings_30_days' => $therapists->sum(function($therapist) {
                return $therapist->bookings->count();
            }),
            'average_rating' => $therapists->avg('rating'),
            'top_therapists' => $therapists->sortByDesc('rating')->take(5)->map(function($therapist) {
                return [
                    'id' => $therapist->id,
                    'name' => $therapist->name,
                    'specialization' => $therapist->specialization,
                    'rating' => $therapist->rating,
                    'booking_count' => $therapist->bookings->count(),
                    'is_active' => $therapist->is_active
                ];
            })->values()
        ];
        
        return $therapistStats;
    }

    /**
     * Fetch specific user data
     */
    private function fetchSpecificUserData(int $userId): array
    {
        $user = User::with(['verification', 'profile'])->find($userId);
        
        if (!$user) {
            return ['error' => 'User not found'];
        }
        
        $tierService = app(\App\Services\UserTierService::class);
        $today = now()->format('Y-m-d');
        
        // Get user activities
        $activities = DB::table('user_daily_activities')
            ->where('user_id', $userId)
            ->where('date', $today)
            ->pluck('count', 'activity')
            ->toArray();
        
        // Get user tier and limits
        $tier = $tierService->getUserTier($user);
        $limits = $tierService->getUserLimits($user);
        
        // Get recent activity history (last 7 days)
        $recentActivity = DB::table('user_daily_activities')
            ->where('user_id', $userId)
            ->where('date', '>=', now()->subDays(7)->format('Y-m-d'))
            ->orderBy('date', 'desc')
            ->get()
            ->groupBy('date');
        
        return [
            'user_profile' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'tier' => $tier,
                'is_verified' => $user->is_verified,
                'created_at' => $user->created_at,
                'subscription_plan' => $user->subscription_plan,
                'subscription_status' => $user->subscription_status,
                'subscription_expires_at' => $user->subscription_expires_at,
            ],
            'activities' => $activities,
            'limits' => $limits,
            'recent_activity_history' => $recentActivity
        ];
    }

    /**
     * Fetch user by name
     */
    private function fetchUserByName(string $name): array
    {
        $users = User::where('name', 'LIKE', "%{$name}%")
            ->limit(5)
            ->get(['id', 'name', 'email', 'created_at', 'subscription_plan']);
        
        if ($users->isEmpty()) {
            return ['error' => 'No users found with that name'];
        }
        
        if ($users->count() === 1) {
            return $this->fetchSpecificUserData($users->first()->id);
        }
        
        return [
            'multiple_users' => $users->toArray(),
            'message' => 'Multiple users found. Please specify which one:'
        ];
    }

    /**
     * Fetch activity analysis
     */
    private function fetchActivityAnalysis(array $analysis): array
    {
        $timeRange = $analysis['time_range'];
        $limit = $analysis['limit'] ?? 10;
        
        $dateFilter = $this->getDateFilter($timeRange);
        
        // Get most active users
        $activeUsers = DB::table('user_daily_activities')
            ->join('users', 'user_daily_activities.user_id', '=', 'users.id')
            ->where('user_daily_activities.date', '>=', $dateFilter)
            ->select('users.id', 'users.name', 'users.email', 
                    DB::raw('SUM(user_daily_activities.count) as total_activity'))
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderBy('total_activity', 'desc')
            ->limit($limit)
            ->get();
        
        // Get activity breakdown
        $activityBreakdown = DB::table('user_daily_activities')
            ->where('date', '>=', $dateFilter)
            ->select('activity', DB::raw('SUM(count) as total'))
            ->groupBy('activity')
            ->orderBy('total', 'desc')
            ->get()
            ->pluck('total', 'activity');
        
        return [
            'statistics' => [
                'time_range' => $timeRange,
                'total_active_users' => $activeUsers->count(),
                'total_activities' => $activityBreakdown->sum()
            ],
            'top_users' => $activeUsers->toArray(),
            'activity_breakdown' => $activityBreakdown->toArray()
        ];
    }

    /**
     * Fetch users at limits
     */
    private function fetchUsersAtLimits(): array
    {
        $today = now()->format('Y-m-d');
        
        // Users at profile view limit (90% of limit)
        $usersAtViewLimit = DB::table('user_daily_activities')
            ->join('users', 'user_daily_activities.user_id', '=', 'users.id')
            ->where('user_daily_activities.activity', 'profile_views')
            ->where('user_daily_activities.date', $today)
            ->where('user_daily_activities.count', '>=', 63) // 90% of 70
            ->where(function($query) {
                $query->whereNull('users.subscription_plan')
                      ->orWhere('users.subscription_status', '!=', 'active');
            })
            ->select('users.id', 'users.name', 'users.email', 'user_daily_activities.count')
            ->get();
        
        // Users at message limit
        $usersAtMessageLimit = DB::table('user_daily_activities')
            ->join('users', 'user_daily_activities.user_id', '=', 'users.id')
            ->where('user_daily_activities.activity', 'messages_sent')
            ->where('user_daily_activities.date', $today)
            ->where('user_daily_activities.count', '>=', 25) // Near basic limit
            ->select('users.id', 'users.name', 'users.email', 'user_daily_activities.count')
            ->get();
        
        return [
            'users_at_view_limit' => $usersAtViewLimit->toArray(),
            'users_at_message_limit' => $usersAtMessageLimit->toArray(),
            'statistics' => [
                'view_limit_count' => $usersAtViewLimit->count(),
                'message_limit_count' => $usersAtMessageLimit->count()
            ]
        ];
    }

    /**
     * Fetch tier analysis
     */
    private function fetchTierAnalysis(string $timeRange): array
    {
        // Get tier distribution
        $tierStats = [
            'free_users' => User::where(function($query) {
                $query->whereNull('subscription_plan')
                      ->orWhere('subscription_status', '!=', 'active');
            })->count(),
            'basic_users' => User::where('subscription_plan', 'basic')
                ->where('subscription_status', 'active')->count(),
            'gold_users' => User::where('subscription_plan', 'gold')
                ->where('subscription_status', 'active')->count(),
            'platinum_users' => User::where('subscription_plan', 'platinum')
                ->where('subscription_status', 'active')->count(),
        ];
        
        // Get recent conversions (last 30 days)
        $recentConversions = User::whereNotNull('subscription_plan')
            ->where('subscription_status', 'active')
            ->where('created_at', '>=', now()->subDays(30))
            ->count();
        
        return [
            'statistics' => $tierStats,
            'recent_conversions' => $recentConversions,
            'conversion_rate' => $tierStats['free_users'] > 0 ? 
                round(($recentConversions / $tierStats['free_users']) * 100, 2) : 0
        ];
    }

    /**
     * Fetch upgrade candidates
     */
    private function fetchUpgradeCandidates(int $limit): array
    {
        $today = now()->format('Y-m-d');
        
        // Free users with high activity
        $candidates = DB::table('user_daily_activities')
            ->join('users', 'user_daily_activities.user_id', '=', 'users.id')
            ->where('user_daily_activities.date', $today)
            ->where(function($query) {
                $query->whereNull('users.subscription_plan')
                      ->orWhere('users.subscription_status', '!=', 'active');
            })
            ->select('users.id', 'users.name', 'users.email', 
                    DB::raw('SUM(user_daily_activities.count) as total_activity'))
            ->groupBy('users.id', 'users.name', 'users.email')
            ->having('total_activity', '>=', 50) // High activity threshold
            ->orderBy('total_activity', 'desc')
            ->limit($limit)
            ->get();
        
        return [
            'upgrade_candidates' => $candidates->toArray(),
            'statistics' => [
                'candidate_count' => $candidates->count(),
                'avg_activity' => $candidates->avg('total_activity')
            ]
        ];
    }

    /**
     * Fetch general analytics
     */
    private function fetchGeneralAnalytics(string $timeRange): array
    {
        $dateFilter = $this->getDateFilter($timeRange);
        
        $stats = [
            'total_users' => User::count(),
            'verified_users' => User::where('is_verified', true)->count(),
            'premium_users' => User::whereNotNull('subscription_plan')
                ->where('subscription_status', 'active')->count(),
            'daily_activities' => DB::table('user_daily_activities')
                ->where('date', '>=', $dateFilter)
                ->sum('count'),
            'active_users_period' => DB::table('user_daily_activities')
                ->where('date', '>=', $dateFilter)
                ->distinct('user_id')
                ->count()
        ];
        
        return ['statistics' => $stats];
    }

    /**
     * Get date filter for time range
     */
    private function getDateFilter(string $timeRange): string
    {
        switch ($timeRange) {
            case 'yesterday':
                return now()->subDay()->format('Y-m-d');
            case 'week':
                return now()->subWeek()->format('Y-m-d');
            case 'month':
                return now()->subMonth()->format('Y-m-d');
            case 'year':
                return now()->subYear()->format('Y-m-d');
            case 'today':
            default:
                return now()->format('Y-m-d');
        }
    }

    /**
     * Generate AI response for user insights
     */
    private function generateUserInsightResponse(string $query, array $userData, array $conversationHistory): array
    {
        try {
            $openAIService = app(\App\Services\OpenAIService::class);
            
            $prompt = $this->buildUserInsightPrompt($query, $userData, $conversationHistory);
            
            $messages = [
                ['role' => 'system', 'content' => 'You are an AI assistant helping admin analyze user data for ZawajAfrica dating platform. Provide clear, actionable insights based on the data provided.'],
                ['role' => 'user', 'content' => $prompt]
            ];
            
            $response = $openAIService->chat($messages);
            
            return $response;
            
        } catch (\Exception $e) {
            Log::error('Failed to generate user insight response', [
                'error' => $e->getMessage(),
                'query' => $query
            ]);
            
            return [
                'success' => false,
                'error' => 'Failed to generate AI response'
            ];
        }
    }

    /**
     * Build prompt for user insights
     */
    private function buildUserInsightPrompt(string $query, array $userData, array $conversationHistory): string
    {
        $dataJson = json_encode($userData, JSON_PRETTY_PRINT);
        
        $contextHistory = '';
        if (!empty($conversationHistory)) {
            $recentMessages = array_slice($conversationHistory, -4); // Last 4 messages
            $contextHistory = "\n\nRecent conversation context:\n";
            foreach ($recentMessages as $msg) {
                $contextHistory .= "- {$msg['role']}: " . substr($msg['content'], 0, 100) . "\n";
            }
        }
        
        return "Admin Query: {$query}

User Data:
{$dataJson}

{$contextHistory}

You have access to comprehensive platform data including user activities, therapist information, and platform statistics. 

Therapist Information Available:
- Total therapists: {$userData['therapist_info']['total_therapists']}
- Active therapists: {$userData['therapist_info']['active_therapists']}
- Total bookings (30 days): {$userData['therapist_info']['total_bookings_30_days']}
- Average therapist rating: {$userData['therapist_info']['average_rating']}
- Top 5 therapists by rating are also available in the data

Please analyze this data and provide insights that answer the admin's query. Include:
1. Direct answer to the question
2. Key patterns or trends observed
3. Actionable recommendations
4. Any concerns or opportunities identified
5. If relevant, include insights about therapist utilization and performance

Keep your response conversational but professional, and focus on actionable insights for platform management.";
    }

    /**
     * Import users to Zoho Campaign
     */
    public function importUsersToZoho(Request $request)
    {
        $request->validate([
            'target_audience' => 'required|string|in:all,premium,basic,free',
            'list_name' => 'nullable|string|max:255',
            'existing_list_key' => 'nullable|string|max:100'
        ]);

        try {
            $zohoCampaignService = new \App\Services\ZohoCampaignService();

            if (!$zohoCampaignService->isConfigured()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Zoho Campaign is not properly configured. Please check your environment variables.'
                ], 500);
            }

            $result = $zohoCampaignService->importUsers(
                $request->target_audience,
                $request->list_name,
                $request->existing_list_key
            );

            Log::info('Zoho Campaign import request', [
                'admin_id' => auth()->id(),
                'target_audience' => $request->target_audience,
                'result' => $result
            ]);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Failed to import users to Zoho Campaign', [
                'admin_id' => auth()->id(),
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Zoho Campaign mailing lists
     */
    public function getMailingLists()
    {
        try {
            $zohoCampaignService = new \App\Services\ZohoCampaignService();

            if (!$zohoCampaignService->isConfigured()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Zoho Campaign is not properly configured. Please check your environment variables.'
                ], 500);
            }

            $result = $zohoCampaignService->getMailingLists();
            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Failed to fetch Zoho Campaign mailing lists', [
                'admin_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch mailing lists: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create and send a Zoho Campaign
     */
    public function createCampaign(Request $request)
    {
        $request->validate([
            'list_key' => 'required|string',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'campaign_name' => 'nullable|string|max:255',
            'send_immediately' => 'nullable|boolean'
        ]);

        try {
            $zohoCampaignService = new \App\Services\ZohoCampaignService();

            if (!$zohoCampaignService->isConfigured()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Zoho Campaign is not properly configured. Please check your environment variables.'
                ], 500);
            }

            $sendImmediately = $request->boolean('send_immediately', false);

            if ($sendImmediately) {
                // Use the createAndSendCampaign method for immediate sending
                $result = $zohoCampaignService->createAndSendCampaign(
                    $request->list_key,
                    $request->subject,
                    $request->content,
                    $request->campaign_name,
                    true
                );
            } else {
                // Just create the campaign as draft
                $result = $zohoCampaignService->createCampaign(
                    $request->list_key,
                    $request->subject,
                    $request->content,
                    $request->campaign_name
                );
            }

            Log::info('Zoho Campaign creation request', [
                'admin_id' => auth()->id(),
                'list_key' => $request->list_key,
                'subject' => $request->subject,
                'send_immediately' => $sendImmediately,
                'result' => $result
            ]);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Failed to create Zoho Campaign', [
                'admin_id' => auth()->id(),
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Campaign creation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send an existing Zoho Campaign
     */
    public function sendCampaign(Request $request)
    {
        $request->validate([
            'campaign_key' => 'required|string'
        ]);

        try {
            $zohoCampaignService = new \App\Services\ZohoCampaignService();

            if (!$zohoCampaignService->isConfigured()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Zoho Campaign is not properly configured. Please check your environment variables.'
                ], 500);
            }

            $result = $zohoCampaignService->sendCampaign($request->campaign_key);

            Log::info('Zoho Campaign send request', [
                'admin_id' => auth()->id(),
                'campaign_key' => $request->campaign_key,
                'result' => $result
            ]);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Failed to send Zoho Campaign', [
                'admin_id' => auth()->id(),
                'error' => $e->getMessage(),
                'campaign_key' => $request->campaign_key ?? 'unknown'
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Campaign sending failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Zoho Campaign statistics
     */
    public function getCampaignStats(string $campaignKey)
    {
        try {
            $zohoCampaignService = new \App\Services\ZohoCampaignService();

            if (!$zohoCampaignService->isConfigured()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Zoho Campaign is not properly configured. Please check your environment variables.'
                ], 500);
            }

            $result = $zohoCampaignService->getCampaignStats($campaignKey);
            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Failed to fetch Zoho Campaign stats', [
                'admin_id' => auth()->id(),
                'campaign_key' => $campaignKey,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch campaign stats: ' . $e->getMessage()
            ], 500);
        }
    }

} 
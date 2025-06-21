<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class NotificationController extends Controller
{
    /**
     * Display notifications page
     */
    public function page()
    {
        return Inertia::render('Notifications/Index');
    }

    /**
     * Get user's notifications
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = $user->notifications();
        
        // Filter by read/unread status
        if ($request->has('filter')) {
            if ($request->filter === 'unread') {
                $query->whereNull('read_at');
            } elseif ($request->filter === 'read') {
                $query->whereNotNull('read_at');
            }
        }
        
        // Filter by type
        if ($request->has('type') && !empty($request->type)) {
            $query->where('data->type', $request->type);
        }
        
        $notifications = $query->orderBy('created_at', 'desc')
            ->paginate(20);
        
        // Get notification counts
        $counts = [
            'total' => $user->notifications()->count(),
            'unread' => $user->unreadNotifications()->count(),
            'read' => $user->readNotifications()->count(),
        ];
        
        // Temporarily removed type counts due to MySQL ONLY_FULL_GROUP_BY mode issues
        // This feature can be re-implemented later if needed
        $typeCounts = [];
        
        return response()->json([
            'notifications' => $notifications,
            'counts' => $counts,
            'typeCounts' => $typeCounts,
        ]);
    }
    
    /**
     * Get unread notifications for header dropdown
     */
    public function unread()
    {
        $user = Auth::user();
        
        $notifications = $user->unreadNotifications()
            ->take(10)
            ->get();
        
        $unreadCount = $user->unreadNotifications()->count();
        
        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }
    
    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        $user = Auth::user();
        
        $notification = $user->notifications()->find($id);
        
        if ($notification) {
            $notification->markAsRead();
            return response()->json(['success' => true]);
        }
        
        return response()->json(['error' => 'Notification not found'], 404);
    }
    
    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        
        $user->unreadNotifications->markAsRead();
        
        return response()->json(['success' => true]);
    }
    
    /**
     * Delete notification
     */
    public function destroy($id)
    {
        $user = Auth::user();
        
        $notification = $user->notifications()->find($id);
        
        if ($notification) {
            $notification->delete();
            return response()->json(['success' => true]);
        }
        
        return response()->json(['error' => 'Notification not found'], 404);
    }
    
    /**
     * Delete all read notifications
     */
    public function clearRead()
    {
        $user = Auth::user();
        
        $user->readNotifications()->delete();
        
        return response()->json(['success' => true]);
    }
    
    /**
     * Get notification settings
     */
    public function getSettings()
    {
        $user = Auth::user();
        
        // Get user's notification preferences (you might want to add this to user table)
        $settings = [
            'email_new_matches' => true,
            'email_new_messages' => true,
            'email_booking_updates' => true,
            'email_verification_updates' => true,
            'push_new_matches' => true,
            'push_new_messages' => true,
            'push_booking_updates' => true,
            'push_profile_views' => false,
        ];
        
        return response()->json(['settings' => $settings]);
    }
    
    /**
     * Update notification settings
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
        ]);
        
        $user = Auth::user();
        
        // Store settings (you might want to add a notification_settings JSON column to users table)
        // For now, we'll use cache
        cache()->put("notification_settings_{$user->id}", $request->settings, 60 * 60 * 24 * 365); // 1 year
        
        return response()->json(['success' => true]);
    }
    

} 
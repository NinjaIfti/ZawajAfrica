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
        
        // Get type counts with proper grouping
        try {
            $typeCounts = $user->notifications()
                ->selectRaw('JSON_UNQUOTE(JSON_EXTRACT(data, "$.type")) as type, COUNT(*) as count')
                ->whereNotNull('data')
                ->groupBy(DB::raw('JSON_UNQUOTE(JSON_EXTRACT(data, "$.type"))'))
                ->pluck('count', 'type')
                ->toArray();
        } catch (\Exception $e) {
            // Fallback if JSON extraction fails
            $typeCounts = [];
        }
        
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
        
        // Get user's notification preferences with defaults
        $settings = [
            'email_new_matches' => $user->email_new_matches ?? true,
            'email_new_messages' => $user->email_new_messages ?? true,
            'email_birthday_reminders' => $user->email_birthday_reminders ?? true,
            'email_subscription_updates' => $user->email_subscription_updates ?? true,
            'email_booking_updates' => $user->email_booking_updates ?? true,
            'push_new_matches' => $user->push_new_matches ?? true,
            'push_new_messages' => $user->push_new_messages ?? true,
            'push_birthday_wishes' => $user->push_birthday_wishes ?? true,
            'push_subscription_updates' => $user->push_subscription_updates ?? true,
            'push_profile_views' => $user->push_profile_views ?? false,
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
            'settings.email_new_matches' => 'boolean',
            'settings.email_new_messages' => 'boolean',
            'settings.email_birthday_reminders' => 'boolean',
            'settings.email_subscription_updates' => 'boolean',
            'settings.email_booking_updates' => 'boolean',
            'settings.push_new_matches' => 'boolean',
            'settings.push_new_messages' => 'boolean',
            'settings.push_birthday_wishes' => 'boolean',
            'settings.push_subscription_updates' => 'boolean',
            'settings.push_profile_views' => 'boolean',
        ]);
        
        $user = Auth::user();
        
        // Update user's notification preferences
        // Note: You should add these columns to the users table migration
        try {
            $user->update($request->settings);
            
            \Log::info('Notification settings updated', [
                'user_id' => $user->id,
                'settings' => $request->settings
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Notification settings updated successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to update notification settings', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update settings'
            ], 500);
        }
    }
    

} 
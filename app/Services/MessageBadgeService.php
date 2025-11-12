<?php

namespace App\Services;

use App\Models\User;
use App\Models\Message;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MessageBadgeService
{
    const CACHE_TTL = 300; // 5 minutes cache
    
    /**
     * Get unread message count for a user
     */
    public function getUnreadCount(User $user): int
    {
        $cacheKey = "unread_messages_count:{$user->id}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user) {
            return $user->unreadMessages()->count();
        });
    }
    
    /**
     * Get unread message count by conversation
     */
    public function getUnreadCountByConversation(User $user): array
    {
        $cacheKey = "unread_messages_by_conversation:{$user->id}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user) {
            return Message::where('receiver_id', $user->id)
                ->where('is_read', false)
                ->selectRaw('sender_id, COUNT(*) as count')
                ->groupBy('sender_id')
                ->pluck('count', 'sender_id')
                ->toArray();
        });
    }
    
    /**
     * Update badge count after message is sent
     */
    public function updateBadgeAfterSend(User $sender, User $receiver): void
    {
        // Clear receiver's cache since they have a new unread message
        $this->clearUserCache($receiver);
        
        // Log for debugging
        Log::info('Badge updated after message sent', [
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'new_unread_count' => $this->getUnreadCount($receiver)
        ]);
    }
    
    /**
     * Update badge count after message is read
     */
    public function updateBadgeAfterRead(User $user, ?User $sender = null): void
    {
        // Clear user's cache since they read messages
        $this->clearUserCache($user);
        
        // Log for debugging
        Log::info('Badge updated after message read', [
            'user_id' => $user->id,
            'sender_id' => $sender?->id,
            'remaining_unread_count' => $this->getUnreadCount($user)
        ]);
    }
    
    /**
     * Clear cache for a specific user
     */
    public function clearUserCache(User $user): void
    {
        Cache::forget("unread_messages_count:{$user->id}");
        Cache::forget("unread_messages_by_conversation:{$user->id}");
    }
    
    /**
     * Get badge data for frontend
     */
    public function getBadgeData(User $user): array
    {
        $unreadCount = $this->getUnreadCount($user);
        $conversationCounts = $this->getUnreadCountByConversation($user);
        
        return [
            'total_unread' => $unreadCount,
            'conversation_counts' => $conversationCounts,
            'has_unread' => $unreadCount > 0,
            'display_count' => $unreadCount > 99 ? '99+' : (string)$unreadCount,
            'timestamp' => now()->toISOString()
        ];
    }
    
    /**
     * Get PWA badge count (for app icon)
     */
    public function getPWABadgeCount(User $user): int
    {
        return $this->getUnreadCount($user);
    }
    
    /**
     * Mark messages as read and update badge
     */
    public function markMessagesAsRead(User $user, User $sender): int
    {
        $updatedCount = Message::where('receiver_id', $user->id)
            ->where('sender_id', $sender->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);
        
        if ($updatedCount > 0) {
            $this->updateBadgeAfterRead($user, $sender);
        }
        
        return $updatedCount;
    }
    
    /**
     * Get recent unread messages for notifications
     */
    public function getRecentUnreadMessages(User $user, int $limit = 5): array
    {
        return Message::where('receiver_id', $user->id)
            ->where('is_read', false)
            ->with(['sender:id,name,profile_photo'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'sender' => [
                        'id' => $message->sender->id,
                        'name' => $message->sender->name,
                        'profile_photo' => $message->sender->profile_photo ? 
                            asset('storage/' . $message->sender->profile_photo) : null
                    ],
                    'content' => $this->truncateMessage($message->content),
                    'created_at' => $message->created_at->diffForHumans(),
                    'timestamp' => $message->created_at->toISOString()
                ];
            })
            ->toArray();
    }
    
    /**
     * Truncate message content for notifications
     */
    private function truncateMessage(string $content, int $length = 50): string
    {
        return strlen($content) > $length ? 
            substr($content, 0, $length) . '...' : 
            $content;
    }
    
    /**
     * Schedule badge update (for real-time updates)
     */
    public function scheduleBadgeUpdate(User $user): void
    {
        // This can be used with broadcasting/websockets for real-time updates
        // For now, we'll just clear the cache to force refresh
        $this->clearUserCache($user);
    }
    
    /**
     * Get notification settings for user
     */
    public function getNotificationSettings(User $user): array
    {
        return [
            'browser_notifications' => true, // Can be stored in user preferences
            'badge_notifications' => true,
            'sound_notifications' => true,
            'desktop_notifications' => true
        ];
    }
}

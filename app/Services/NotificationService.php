<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;

class NotificationService
{
    /**
     * Send notification with fallback mechanisms
     */
    public function sendWithFallback($user, $notification, array $options = []): bool
    {
        $attempts = 0;
        $maxAttempts = 3;
        $success = false;

        while ($attempts < $maxAttempts && !$success) {
            try {
                $attempts++;
                
                // Primary method: Laravel notification system
                $user->notify($notification);
                $success = true;
                
                Log::info('Notification sent successfully', [
                    'user_id' => $user->id,
                    'notification_type' => get_class($notification),
                    'attempt' => $attempts
                ]);

            } catch (\Exception $e) {
                Log::error('Notification delivery failed', [
                    'user_id' => $user->id,
                    'notification_type' => get_class($notification),
                    'attempt' => $attempts,
                    'error' => $e->getMessage()
                ]);

                if ($attempts >= $maxAttempts) {
                    // Try fallback methods
                    $success = $this->tryFallbackMethods($user, $notification);
                } else {
                    // Wait before retry
                    sleep(2 * $attempts);
                }
            }
        }

        return $success;
    }

    /**
     * Try alternative notification delivery methods
     */
    private function tryFallbackMethods($user, $notification): bool
    {
        // Fallback 1: Store in database for manual processing
        try {
            \DB::table('failed_notifications')->insert([
                'user_id' => $user->id,
                'user_email' => $user->email,
                'notification_type' => get_class($notification),
                'notification_data' => json_encode($notification->toArray($user)),
                'failed_at' => now(),
                'retry_count' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            Log::info('Notification stored for manual processing', [
                'user_id' => $user->id,
                'notification_type' => get_class($notification)
            ]);

            return true;

        } catch (\Exception $e) {
            Log::critical('All notification delivery methods failed', [
                'user_id' => $user->id,
                'notification_type' => get_class($notification),
                'final_error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Retry failed notifications from database
     */
    public function retryFailedNotifications(int $limit = 50): int
    {
        $failedNotifications = \DB::table('failed_notifications')
            ->where('retry_count', '<', 3)
            ->where('failed_at', '>', now()->subDays(7)) // Only retry within 7 days
            ->limit($limit)
            ->get();

        $successCount = 0;

        foreach ($failedNotifications as $failedNotification) {
            try {
                $user = \App\Models\User::find($failedNotification->user_id);
                if (!$user) {
                    // User no longer exists, mark as processed
                    \DB::table('failed_notifications')
                        ->where('id', $failedNotification->id)
                        ->delete();
                    continue;
                }

                // Create notification instance from stored data
                $notificationClass = $failedNotification->notification_type;
                $notificationData = json_decode($failedNotification->notification_data, true);

                // Send simple email directly
                Mail::raw($notificationData['message'] ?? 'Notification from ZawajAfrica', function ($message) use ($user, $notificationData) {
                    $message->to($user->email)
                           ->subject($notificationData['title'] ?? 'ZawajAfrica Notification');
                });

                // Mark as successfully processed
                \DB::table('failed_notifications')
                    ->where('id', $failedNotification->id)
                    ->delete();

                $successCount++;

                Log::info('Failed notification retried successfully', [
                    'notification_id' => $failedNotification->id,
                    'user_id' => $user->id
                ]);

            } catch (\Exception $e) {
                // Increment retry count
                \DB::table('failed_notifications')
                    ->where('id', $failedNotification->id)
                    ->update([
                        'retry_count' => $failedNotification->retry_count + 1,
                        'last_retry_at' => now(),
                        'last_error' => $e->getMessage()
                    ]);

                Log::error('Failed notification retry failed', [
                    'notification_id' => $failedNotification->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $successCount;
    }
} 
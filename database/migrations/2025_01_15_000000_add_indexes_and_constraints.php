<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add indexes for better query performance (only if they don't exist)
        Schema::table('therapist_bookings', function (Blueprint $table) {
            try {
                // Performance indexes
                if (!$this->indexExists('therapist_bookings', 'idx_user_status')) {
                    $table->index(['user_id', 'status'], 'idx_user_status');
                }
                if (!$this->indexExists('therapist_bookings', 'idx_therapist_status')) {
                    $table->index(['therapist_id', 'status'], 'idx_therapist_status');
                }
                if (!$this->indexExists('therapist_bookings', 'idx_datetime_status')) {
                    $table->index(['appointment_datetime', 'status'], 'idx_datetime_status');
                }
                if (!$this->indexExists('therapist_bookings', 'idx_payment_reference')) {
                    $table->index('payment_reference', 'idx_payment_reference');
                }
                if (!$this->indexExists('therapist_bookings', 'idx_payment_status')) {
                    $table->index('payment_status', 'idx_payment_status');
                }
                if (!$this->indexExists('therapist_bookings', 'idx_created_at')) {
                    $table->index('created_at', 'idx_created_at');
                }
                if (!$this->indexExists('therapist_bookings', 'idx_zoho_booking_id')) {
                    $table->index('zoho_booking_id', 'idx_zoho_booking_id');
                }
                
                // Composite index for availability checking
                if (!$this->indexExists('therapist_bookings', 'idx_availability_check')) {
                    $table->index(['therapist_id', 'appointment_datetime', 'status'], 'idx_availability_check');
                }
            } catch (\Exception $e) {
                // Index creation might fail if already exists
            }
        });

        Schema::table('therapists', function (Blueprint $table) {
            try {
                if (!$this->indexExists('therapists', 'idx_therapist_status')) {
                    $table->index('status', 'idx_therapist_status');
                }
                if (!$this->indexExists('therapists', 'idx_hourly_rate')) {
                    $table->index('hourly_rate', 'idx_hourly_rate');
                }
                if (!$this->indexExists('therapists', 'idx_therapist_created')) {
                    $table->index('created_at', 'idx_therapist_created');
                }
            } catch (\Exception $e) {
                // Index creation might fail if already exists
            }
        });

        Schema::table('user_matches', function (Blueprint $table) {
            try {
                if (!$this->indexExists('user_matches', 'idx_user_match')) {
                    $table->index(['user1_id', 'user2_id'], 'idx_user_match');
                }
                if (!$this->indexExists('user_matches', 'idx_match_created')) {
                    $table->index('created_at', 'idx_match_created');
                }
            } catch (\Exception $e) {
                // Index creation might fail if already exists
            }
        });

        Schema::table('user_likes', function (Blueprint $table) {
            try {
                if (!$this->indexExists('user_likes', 'idx_user_like')) {
                    $table->index(['user_id', 'liked_user_id'], 'idx_user_like');
                }
                if (!$this->indexExists('user_likes', 'idx_like_created')) {
                    $table->index('created_at', 'idx_like_created');
                }
            } catch (\Exception $e) {
                // Index creation might fail if already exists
            }
        });

        Schema::table('messages', function (Blueprint $table) {
            try {
                if (!$this->indexExists('messages', 'idx_message_participants')) {
                    $table->index(['sender_id', 'receiver_id'], 'idx_message_participants');
                }
                if (!$this->indexExists('messages', 'idx_message_created')) {
                    $table->index('created_at', 'idx_message_created');
                }
                if (!$this->indexExists('messages', 'idx_message_read')) {
                    $table->index('is_read', 'idx_message_read');
                }
            } catch (\Exception $e) {
                // Index creation might fail if already exists
            }
        });

        Schema::table('notifications', function (Blueprint $table) {
            try {
                if (!$this->indexExists('notifications', 'idx_notifiable')) {
                    $table->index(['notifiable_id', 'notifiable_type'], 'idx_notifiable');
                }
                if (!$this->indexExists('notifications', 'idx_read_at')) {
                    $table->index('read_at', 'idx_read_at');
                }
                if (!$this->indexExists('notifications', 'idx_notification_created')) {
                    $table->index('created_at', 'idx_notification_created');
                }
            } catch (\Exception $e) {
                // Index creation might fail if already exists
            }
        });
    }
    
    /**
     * Check if an index exists on a table
     */
    private function indexExists(string $tableName, string $indexName): bool
    {
        return collect(\DB::select("SHOW INDEX FROM {$tableName}"))->contains('Key_name', $indexName);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('therapist_bookings', function (Blueprint $table) {
            $table->dropIndex('idx_user_status');
            $table->dropIndex('idx_therapist_status');
            $table->dropIndex('idx_datetime_status');
            $table->dropIndex('idx_payment_reference');
            $table->dropIndex('idx_payment_status');
            $table->dropIndex('idx_created_at');
            $table->dropIndex('idx_zoho_booking_id');
            $table->dropIndex('idx_availability_check');
        });

        Schema::table('therapists', function (Blueprint $table) {
            $table->dropIndex('idx_therapist_status');
            $table->dropIndex('idx_hourly_rate');
            $table->dropIndex('idx_therapist_created');
        });

        Schema::table('user_matches', function (Blueprint $table) {
            $table->dropIndex('idx_user_match');
            $table->dropIndex('idx_match_created');
        });

        Schema::table('user_likes', function (Blueprint $table) {
            $table->dropIndex('idx_user_like');
            $table->dropIndex('idx_like_created');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex('idx_message_participants');
            $table->dropIndex('idx_message_created');
            $table->dropIndex('idx_message_read');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('idx_notifiable');
            $table->dropIndex('idx_read_at');
            $table->dropIndex('idx_notification_created');
        });
    }
}; 
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->ensureProfileFields();
        $this->ensureLegacyAmounts();
        $this->ensureIndexes();
    }

    /**
     * This migration intentionally uses roll-forward recovery. Its fields and
     * indexes may already contain production data and are not deleted on rollback.
     */
    public function down(): void
    {
        // No destructive rollback.
    }

    private function ensureProfileFields(): void
    {
        if (Schema::hasTable('user_backgrounds')) {
            Schema::table('user_backgrounds', function (Blueprint $table) {
                if (!Schema::hasColumn('user_backgrounds', 'ethnic_group')) {
                    $table->string('ethnic_group')->nullable()->after('nationality');
                }
                if (!Schema::hasColumn('user_backgrounds', 'islamic_affiliation')) {
                    $table->string('islamic_affiliation')->nullable()->after('ethnic_group');
                }
            });
        }

        if (Schema::hasTable('user_lifestyles') && !Schema::hasColumn('user_lifestyles', 'want_children')) {
            Schema::table('user_lifestyles', function (Blueprint $table) {
                $table->string('want_children')->nullable()->after('number_of_children');
            });
        }
    }

    private function ensureLegacyAmounts(): void
    {
        if (Schema::hasTable('therapist_bookings') && Schema::hasColumn('therapist_bookings', 'amount')) {
            DB::table('therapist_bookings')->whereNull('amount')->update(['amount' => 0]);
            Schema::table('therapist_bookings', function (Blueprint $table) {
                $table->decimal('amount', 10, 2)->default(0)->change();
            });
        }

        if (Schema::hasTable('therapists') && Schema::hasColumn('therapists', 'hourly_rate')) {
            DB::table('therapists')->whereNull('hourly_rate')->update(['hourly_rate' => 0]);
            Schema::table('therapists', function (Blueprint $table) {
                $table->decimal('hourly_rate', 8, 2)->default(0)->change();
            });
        }
    }

    private function ensureIndexes(): void
    {
        $this->addIndexes('therapist_bookings', [
            'idx_user_status' => ['user_id', 'status'],
            'idx_therapist_status' => ['therapist_id', 'status'],
            'idx_datetime_status' => ['appointment_datetime', 'status'],
            'idx_payment_reference' => ['payment_reference'],
            'idx_payment_status' => ['payment_status'],
            'idx_created_at' => ['created_at'],
            'idx_zoho_booking_id' => ['zoho_booking_id'],
            'idx_availability_check' => ['therapist_id', 'appointment_datetime', 'status'],
        ]);

        $this->addIndexes('therapists', [
            'idx_therapist_status' => ['status'],
            'idx_hourly_rate' => ['hourly_rate'],
            'idx_therapist_created' => ['created_at'],
        ]);
        $this->addIndexes('user_matches', [
            'idx_user_match' => ['user1_id', 'user2_id'],
            'idx_match_created' => ['created_at'],
        ]);
        $this->addIndexes('user_likes', [
            'idx_user_like' => ['user_id', 'liked_user_id'],
            'idx_like_created' => ['created_at'],
        ]);
        $this->addIndexes('messages', [
            'idx_message_participants' => ['sender_id', 'receiver_id'],
            'idx_message_created' => ['created_at'],
            'idx_message_read' => ['is_read'],
        ]);
        $this->addIndexes('notifications', [
            'idx_notifiable' => ['notifiable_id', 'notifiable_type'],
            'idx_read_at' => ['read_at'],
            'idx_notification_created' => ['created_at'],
        ]);
    }

    /**
     * @param array<string, list<string>> $indexes
     */
    private function addIndexes(string $tableName, array $indexes): void
    {
        if (!Schema::hasTable($tableName)) {
            return;
        }

        foreach ($indexes as $indexName => $columns) {
            if (!collect($columns)->every(fn (string $column) => Schema::hasColumn($tableName, $column))) {
                continue;
            }

            $existingIndexes = collect(Schema::getIndexes($tableName));
            $hasEquivalentIndex = $existingIndexes->contains(
                fn (array $index) => $index['columns'] === $columns
            );
            $safeIndexName = DB::getDriverName() === 'sqlite'
                ? $tableName.'_'.$indexName
                : $indexName;

            if (!$hasEquivalentIndex && !Schema::hasIndex($tableName, $safeIndexName)) {
                Schema::table($tableName, function (Blueprint $table) use ($columns, $safeIndexName) {
                    $table->index($columns, $safeIndexName);
                });
            }
        }
    }
};

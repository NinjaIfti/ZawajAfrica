<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLike extends Model
{
    protected $fillable = [
        'user_id',
        'liked_user_id', 
        'status',
        'liked_at'
    ];

    protected $casts = [
        'liked_at' => 'datetime'
    ];

    /**
     * The user who sent the like
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The user who was liked
     */
    public function likedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'liked_user_id');
    }

    /**
     * Check if there's a mutual like between two users
     */
    public static function isMutualLike(int $userId1, int $userId2): bool
    {
        // Don't change the order - preserve actual sender/receiver relationships
        // Check if user1 liked user2 AND user2 liked user1
        $like1 = self::where('user_id', $userId1)
                    ->where('liked_user_id', $userId2)
                    ->where('status', 'pending')
                    ->whereNotNull('user_id')
                    ->whereNotNull('liked_user_id')
                    ->exists();

        $like2 = self::where('user_id', $userId2)
                    ->where('liked_user_id', $userId1)
                    ->where('status', 'pending')
                    ->whereNotNull('user_id')
                    ->whereNotNull('liked_user_id')
                    ->exists();

        return $like1 && $like2;
    }

    /**
     * Get mutual likes for creating matches
     */
    public static function getMutualLikes(int $userId1, int $userId2): array
    {
        $like1 = self::where('user_id', $userId1)
                    ->where('liked_user_id', $userId2)
                    ->where('status', 'pending')
                    ->first();

        $like2 = self::where('user_id', $userId2)
                    ->where('liked_user_id', $userId1)
                    ->where('status', 'pending')
                    ->first();

        return [$like1, $like2];
    }

    /**
     * Mark likes as matched
     */
    public static function markAsMatched(int $userId1, int $userId2): void
    {
        // Update both likes regardless of order - preserve actual relationships
        self::where('user_id', $userId1)
            ->where('liked_user_id', $userId2)
            ->update(['status' => 'matched']);

        self::where('user_id', $userId2)
            ->where('liked_user_id', $userId1)
            ->update(['status' => 'matched']);
    }
}

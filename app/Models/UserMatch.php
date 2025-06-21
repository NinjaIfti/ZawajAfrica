<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserMatch extends Model
{
    protected $fillable = [
        'user1_id',
        'user2_id',
        'matched_at',
        'status',
        'conversation_starter',
        'compatibility_details'
    ];

    protected $casts = [
        'matched_at' => 'datetime',
        'compatibility_details' => 'array'
    ];

    /**
     * First user in the match
     */
    public function user1(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user1_id');
    }

    /**
     * Second user in the match
     */
    public function user2(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user2_id');
    }

    /**
     * Create a new match between two users
     */
    public static function createMatch(int $userId1, int $userId2, array $compatibilityDetails = null, string $conversationStarter = null): self
    {
        // Ensure user1_id is always smaller than user2_id for consistency
        if ($userId1 > $userId2) {
            [$userId1, $userId2] = [$userId2, $userId1];
        }

        return self::create([
            'user1_id' => $userId1,
            'user2_id' => $userId2,
            'matched_at' => now(),
            'status' => 'active',
            'conversation_starter' => $conversationStarter,
            'compatibility_details' => $compatibilityDetails
        ]);
    }

    /**
     * Get matches for a specific user
     */
    public static function getMatchesForUser(int $userId)
    {
        return self::with(['user1', 'user2'])
            ->where(function($query) use ($userId) {
                $query->where('user1_id', $userId)
                      ->orWhere('user2_id', $userId);
            })
            ->where('status', 'active')
            ->orderBy('matched_at', 'desc')
            ->get()
            ->map(function($match) use ($userId) {
                // Return the other user's details
                $otherUser = $match->user1_id === $userId ? $match->user2 : $match->user1;
                
                return [
                    'match_id' => $match->id,
                    'user' => $otherUser,
                    'matched_at' => $match->matched_at,
                    'conversation_starter' => $match->conversation_starter,
                    'compatibility_details' => $match->compatibility_details
                ];
            });
    }

    /**
     * Check if two users are already matched
     */
    public static function areMatched(int $userId1, int $userId2): bool
    {
        if ($userId1 > $userId2) {
            [$userId1, $userId2] = [$userId2, $userId1];
        }

        return self::where('user1_id', $userId1)
                  ->where('user2_id', $userId2)
                  ->where('status', 'active')
                  ->whereNotNull('user1_id')
                  ->whereNotNull('user2_id')
                  ->exists();
    }

    /**
     * Unmatch two users
     */
    public static function unmatch(int $userId1, int $userId2): bool
    {
        if ($userId1 > $userId2) {
            [$userId1, $userId2] = [$userId2, $userId1];
        }

        return self::where('user1_id', $userId1)
                  ->where('user2_id', $userId2)
                  ->where('status', 'active')
                  ->update(['status' => 'unmatched']);
    }
}

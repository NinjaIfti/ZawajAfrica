<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatbotConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'role',
        'content',
        'model',
        'tokens_used',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array'
    ];

    /**
     * Get the user that owns the conversation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get recent conversations for a user
     */
    public static function getRecentForUser(int $userId, int $limit = 20): array
    {
        return self::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->reverse()
            ->map(function ($conversation) {
                return [
                    'role' => $conversation->role,
                    'content' => $conversation->content,
                    'timestamp' => $conversation->created_at->toISOString(),
                    'model' => $conversation->model,
                    'tokens_used' => $conversation->tokens_used
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Store a conversation turn
     */
    public static function storeTurn(int $userId, string $userMessage, string $botResponse, ?string $model = null, ?array $usage = null): void
    {
        // Store user message
        self::create([
            'user_id' => $userId,
            'role' => 'user',
            'content' => $userMessage,
            'model' => null,
            'tokens_used' => null,
            'metadata' => ['timestamp' => now()->toISOString()]
        ]);

        // Store bot response
        self::create([
            'user_id' => $userId,
            'role' => 'assistant',
            'content' => $botResponse,
            'model' => $model,
            'tokens_used' => $usage['total_tokens'] ?? null,
            'metadata' => [
                'usage' => $usage,
                'timestamp' => now()->toISOString()
            ]
        ]);

        // Clean up old conversations (keep last 100 per user)
        // First get the IDs of the most recent 100 conversations to keep
        $keepIds = self::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->pluck('id');

        // Then delete all conversations except those in the keep list
        if ($keepIds->isNotEmpty()) {
            self::where('user_id', $userId)
                ->whereNotIn('id', $keepIds)
                ->delete();
        }
    }

    /**
     * Clear all conversations for a user
     */
    public static function clearForUser(int $userId): void
    {
        self::where('user_id', $userId)->delete();
    }

    /**
     * Get conversation statistics for admin
     */
    public static function getStatistics(): array
    {
        return [
            'total_conversations' => self::count(),
            'unique_users' => self::distinct('user_id')->count(),
            'avg_messages_per_user' => self::selectRaw('AVG(message_count) as avg')
                ->fromSub(
                    self::selectRaw('user_id, COUNT(*) as message_count')
                        ->groupBy('user_id'),
                    'user_stats'
                )->value('avg'),
            'total_tokens_used' => self::whereNotNull('tokens_used')->sum('tokens_used'),
        ];
    }
} 
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class OpenAIService
{
    private string $apiKey;
    private string $apiUrl;
    private string $model;
    private array $defaultParams;
    private string $systemPrompt;

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key');
        $this->apiUrl = config('services.openai.api_url', 'https://api.openai.com/v1');
        $this->model = config('services.openai.model', 'gpt-4.1-nano');
        $this->systemPrompt = config('services.openai.system_prompt');
        
        $this->defaultParams = [
            'max_tokens' => config('services.openai.max_tokens', 2000),
            'temperature' => config('services.openai.temperature', 0.7),
            'top_p' => config('services.openai.top_p', 1.0),
            'frequency_penalty' => config('services.openai.frequency_penalty', 0.0),
            'presence_penalty' => config('services.openai.presence_penalty', 0.0),
        ];
    }

    /**
     * Generate a chat completion with user context and persona
     */
    public function chat(array $messages, ?int $userId = null, array $userPreferences = []): array
    {
        try {
            // Build conversation with system prompt and user persona
            $conversation = $this->buildConversation($messages, $userId, $userPreferences);
            
            $payload = array_merge([
                'model' => $this->model,
                'messages' => $conversation,
            ], $this->defaultParams);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(60)->post($this->apiUrl . '/chat/completions', $payload);

            if ($response->successful()) {
                $data = $response->json();
                
                // Log successful API call
                Log::info('OpenAI API call successful', [
                    'user_id' => $userId,
                    'model' => $this->model,
                    'usage' => $data['usage'] ?? null,
                ]);

                return [
                    'success' => true,
                    'message' => $data['choices'][0]['message']['content'] ?? '',
                    'usage' => $data['usage'] ?? null,
                    'model' => $data['model'] ?? $this->model,
                ];
            }

            // Handle API errors
            $error = $response->json();
            Log::error('OpenAI API error', [
                'status' => $response->status(),
                'error' => $error,
                'user_id' => $userId,
            ]);

            return [
                'success' => false,
                'error' => $error['error']['message'] ?? 'Unknown API error',
                'code' => $error['error']['code'] ?? 'unknown_error',
            ];

        } catch (\Exception $e) {
            Log::error('OpenAI service exception', [
                'message' => $e->getMessage(),
                'user_id' => $userId,
            ]);

            return [
                'success' => false,
                'error' => 'Service temporarily unavailable',
                'code' => 'service_error',
            ];
        }
    }

    /**
     * Build conversation with system prompt and user persona
     */
    private function buildConversation(array $messages, ?int $userId = null, array $userPreferences = []): array
    {
        $conversation = [];

        // Add system prompt with user persona
        $systemMessage = $this->buildSystemPrompt($userId, $userPreferences);
        $conversation[] = [
            'role' => 'system',
            'content' => $systemMessage,
        ];

        // Add conversation history
        foreach ($messages as $message) {
            $conversation[] = [
                'role' => $message['role'],
                'content' => $message['content'],
            ];
        }

        return $conversation;
    }

    /**
     * Build personalized system prompt based on user data and preferences
     */
    private function buildSystemPrompt(?int $userId = null, array $userPreferences = []): string
    {
        $basePrompt = $this->systemPrompt;
        
        // Add current time context for persona switching (Fatima vs Firdaus)
        $currentHour = now()->format('H');
        $timeContext = "\n\nCurrent time context: It is currently " . now()->format('g:i A') . " (24-hour format: {$currentHour}:00).";
        $basePrompt .= $timeContext;

        if ($userId) {
                    // Get user data for personalization
        $user = \App\Models\User::with(['profile', 'about', 'background', 'lifestyle', 'overview'])
            ->find($userId);

            if ($user) {
                $personalizationContext = $this->buildPersonalizationContext($user, $userPreferences);
                $basePrompt .= "\n\nUser Context:\n" . $personalizationContext;
            }
        }

        // Add ZawajAfrica specific guidelines
        $basePrompt .= "\n\nPlatform Guidelines:";
        $basePrompt .= "\n- Focus on meaningful relationships and cultural compatibility";
        $basePrompt .= "\n- Provide advice on profile optimization for better matches";
        $basePrompt .= "\n- Suggest therapy services when appropriate for relationship issues";
        $basePrompt .= "\n- Be respectful of Islamic values and African cultural diversity";
        $basePrompt .= "\n- Help users navigate the platform features";
        $basePrompt .= "\n- Encourage healthy communication and relationship building";

        return $basePrompt;
    }

    /**
     * Build user personalization context
     */
    private function buildPersonalizationContext($user, array $preferences = []): string
    {
        $context = [];

        // Basic user info
        if ($user->name) {
            $context[] = "User's name: {$user->name}";
        }

        // Profile information
        if ($user->profile) {
            if ($user->profile->age) {
                $context[] = "Age: {$user->profile->age}";
            }
            if ($user->profile->gender) {
                $context[] = "Gender: {$user->profile->gender}";
            }
        }

        // Background information  
        if ($user->overview) {
            if ($user->overview->religion) {
                $context[] = "Religion: {$user->overview->religion}";
            }
        }
        
        if ($user->background) {
            if ($user->background->ethnic_group) {
                $context[] = "Ethnicity: {$user->background->ethnic_group}";
            }
            if ($user->background->education) {
                $context[] = "Education: {$user->background->education}";
            }
        }

        // Lifestyle preferences
        if ($user->lifestyle) {
            if ($user->lifestyle->drinking) {
                $context[] = "Drinking preference: {$user->lifestyle->drinking}";
            }
            if ($user->lifestyle->smoking) {
                $context[] = "Smoking preference: {$user->lifestyle->smoking}";
            }
        }

        // Relationship goals from about section
        if ($user->about && $user->about->relationship_goals) {
            $context[] = "Relationship goals: {$user->about->relationship_goals}";
        }

        // Add custom preferences
        if (!empty($preferences)) {
            $context[] = "User preferences: " . json_encode($preferences);
        }

        return implode("\n", $context);
    }

    /**
     * Get conversation history for a user
     */
    public function getConversationHistory(int $userId, int $limit = 20): array
    {
        $cacheKey = "chatbot_history_{$userId}";
        return Cache::get($cacheKey, []);
    }

    /**
     * Save conversation turn to history
     */
    public function saveConversationTurn(int $userId, string $userMessage, string $botResponse): void
    {
        $cacheKey = "chatbot_history_{$userId}";
        $history = $this->getConversationHistory($userId);

        // Add new conversation turn
        $history[] = [
            'role' => 'user',
            'content' => $userMessage,
            'timestamp' => now()->toISOString(),
        ];
        
        $history[] = [
            'role' => 'assistant',
            'content' => $botResponse,
            'timestamp' => now()->toISOString(),
        ];

        // Keep only recent history (configurable limit)
        $maxHistory = config('services.openai.max_history', 20);
        if (count($history) > $maxHistory) {
            $history = array_slice($history, -$maxHistory);
        }

        // Cache for 24 hours
        Cache::put($cacheKey, $history, 60 * 60 * 24);
    }

    /**
     * Clear conversation history for a user
     */
    public function clearConversationHistory(int $userId): void
    {
        $cacheKey = "chatbot_history_{$userId}";
        Cache::forget($cacheKey);
    }

    /**
     * Check if OpenAI service is available
     */
    public function isAvailable(): bool
    {
        return !empty($this->apiKey) && config('services.openai.enabled', true);
    }

    /**
     * Get suggested conversation starters based on user profile
     */
    public function getSuggestedStarters(int $userId): array
    {
        $user = \App\Models\User::with(['profile', 'about'])->find($userId);
        
        $starters = [
            "How can I improve my profile to attract better matches?",
            "What should I include in my bio to stand out?",
            "How do I start a meaningful conversation with someone?",
            "What are some good first date ideas in my area?",
        ];

        // Add personalized starters based on user data
        if ($user && $user->about) {
            if (empty($user->about->relationship_goals)) {
                $starters[] = "Help me define my relationship goals clearly.";
            }
            
            if ($user->profile && !$user->profile->bio) {
                $starters[] = "Help me write an engaging bio for my profile.";
            }
        }

        return array_slice($starters, 0, 6); // Return max 6 starters
    }
} 
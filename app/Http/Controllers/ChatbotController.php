<?php

namespace App\Http\Controllers;

use App\Services\OpenAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Inertia;

class ChatbotController extends Controller
{
    private OpenAIService $openAIService;

    public function __construct(OpenAIService $openAIService)
    {
        $this->openAIService = $openAIService;
    }

    /**
     * Display the chatbot interface
     */
    public function index()
    {
        $user = Auth::user();
        
        // Check if OpenAI service is available
        if (!$this->openAIService->isAvailable()) {
            return redirect()->route('dashboard')->with('error', 'AI Chatbot is temporarily unavailable.');
        }

        // Get conversation history
        $history = $this->openAIService->getConversationHistory($user->id);
        
        // Get suggested conversation starters
        $suggestedStarters = $this->openAIService->getSuggestedStarters($user->id);

        // Get user profile completion for better context
        $profileCompletion = $this->calculateProfileCompletion($user);

        return Inertia::render('Chatbot/Index', [
            'user' => $user,
            'chatHistory' => $history,
            'suggestedStarters' => $suggestedStarters,
            'profileCompletion' => $profileCompletion,
            'isAvailable' => true,
        ]);
    }

    /**
     * Handle chat message and get AI response
     */
    public function chat(Request $request)
    {
        try {
            \Log::info('Chatbot chat method called', ['user_id' => Auth::id()]);
            
            $user = Auth::user();
            
            // Rate limiting - allow X requests per minute per user
        $rateLimitKey = 'chatbot:' . $user->id;
        $maxAttempts = config('services.openai.rate_limit', 60);
        
        if (RateLimiter::tooManyAttempts($rateLimitKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            return response()->json([
                'success' => false,
                'error' => "Too many requests. Please try again in {$seconds} seconds.",
                'retry_after' => $seconds,
            ], 429);
        }

        RateLimiter::hit($rateLimitKey, 60); // 60 seconds window

        $request->validate([
            'message' => 'required|string|max:2000',
            'preferences' => 'sometimes|array',
        ]);

        // Check if service is available
        if (!$this->openAIService->isAvailable()) {
            return response()->json([
                'success' => false,
                'error' => 'AI Chatbot is temporarily unavailable.',
            ], 503);
        }

        $userMessage = trim($request->message);
        $userPreferences = $request->preferences ?? [];

        // Get recent conversation history
        $history = $this->openAIService->getConversationHistory($user->id);
        
        // Build messages array for API
        $messages = array_slice($history, -10); // Get last 10 messages for context
        $messages[] = [
            'role' => 'user',
            'content' => $userMessage,
        ];

        // Get AI response
        $response = $this->openAIService->chat($messages, $user->id, $userPreferences);

        if ($response['success']) {
            // Save conversation turn to history
            $this->openAIService->saveConversationTurn(
                $user->id,
                $userMessage,
                $response['message']
            );

            return response()->json([
                'success' => true,
                'message' => $response['message'],
                'model' => $response['model'],
                'usage' => $response['usage'] ?? null,
                'timestamp' => now()->toISOString(),
            ]);
        }

        // Handle AI service errors
        return response()->json([
            'success' => false,
            'error' => $response['error'],
            'code' => $response['code'],
        ], 500);
        
        } catch (\Exception $e) {
            \Log::error('Chatbot error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while processing your message.',
                'debug' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear conversation history
     */
    public function clearHistory()
    {
        $user = Auth::user();
        $this->openAIService->clearConversationHistory($user->id);

        return response()->json([
            'success' => true,
            'message' => 'Conversation history cleared successfully.',
        ]);
    }

    /**
     * Get conversation history
     */
    public function getHistory()
    {
        $user = Auth::user();
        $history = $this->openAIService->getConversationHistory($user->id);

        return response()->json([
            'success' => true,
            'history' => $history,
        ]);
    }

    /**
     * Get suggested conversation starters
     */
    public function getStarters()
    {
        $user = Auth::user();
        $starters = $this->openAIService->getSuggestedStarters($user->id);

        return response()->json([
            'success' => true,
            'starters' => $starters,
        ]);
    }

    /**
     * Update user preferences for personalization
     */
    public function updatePreferences(Request $request)
    {
        $request->validate([
            'preferences' => 'required|array',
            'preferences.topics_of_interest' => 'sometimes|array',
            'preferences.communication_style' => 'sometimes|string|in:formal,casual,friendly,professional',
            'preferences.advice_level' => 'sometimes|string|in:basic,detailed,comprehensive',
            'preferences.cultural_context' => 'sometimes|string',
            'preferences.language_preference' => 'sometimes|string',
        ]);

        $user = Auth::user();
        
        // Store preferences in user meta or cache
        $cacheKey = "chatbot_preferences_{$user->id}";
        cache()->put($cacheKey, $request->preferences, 60 * 60 * 24 * 30); // 30 days

        return response()->json([
            'success' => true,
            'message' => 'Preferences updated successfully.',
        ]);
    }

    /**
     * Get user preferences
     */
    public function getPreferences()
    {
        $user = Auth::user();
        $cacheKey = "chatbot_preferences_{$user->id}";
        $preferences = cache()->get($cacheKey, []);

        return response()->json([
            'success' => true,
            'preferences' => $preferences,
        ]);
    }

    /**
     * Calculate profile completion percentage
     */
    private function calculateProfileCompletion($user): int
    {
        $profileCompletion = 0;
        
        if ($user->profile) {
            $totalFields = count($user->profile->getFillable());
            $filledFields = 0;
            
            foreach ($user->profile->getFillable() as $field) {
                if (!empty($user->profile->{$field}) && $field !== 'user_id') {
                    $filledFields++;
                }
            }
            
            if ($totalFields > 0) {
                $profileCompletion = round(($filledFields / ($totalFields - 1)) * 100);
            }
        }
        
        return $profileCompletion;
    }

    /**
     * Get chatbot status and configuration
     */
    public function status()
    {
        return response()->json([
            'available' => $this->openAIService->isAvailable(),
            'model' => config('services.openai.model'),
            'features' => [
                'conversation_history' => true,
                'personalization' => true,
                'suggested_starters' => true,
                'cultural_awareness' => true,
                'relationship_advice' => true,
                'profile_optimization' => true,
            ],
        ]);
    }
} 
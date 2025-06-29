<?php

namespace App\Services;

use App\Models\User;
use App\Models\ChatbotConversation;
use App\Services\OpenAIService;
use App\Services\ZohoMailService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class AIEmailService
{
    private OpenAIService $openAIService;
    private ZohoMailService $zohoMailService;

    public function __construct(OpenAIService $openAIService, ZohoMailService $zohoMailService)
    {
        $this->openAIService = $openAIService;
        $this->zohoMailService = $zohoMailService;
    }

    /**
     * Generate and send welcome email for new user
     */
    public function sendWelcomeEmail(User $user): array
    {
        try {
            $emailContent = $this->generateWelcomeEmailContent($user);
            
            if (!$emailContent['success']) {
                return $emailContent;
            }

            // Use support email for user-facing emails
            $this->zohoMailService->configureMailerWithSender('support');
            $this->sendEmail(
                $user->email,
                $emailContent['subject'],
                $emailContent['body'],
                $user->name
            );

            Log::info('AI welcome email sent successfully', ['user_id' => $user->id]);

            return [
                'success' => true,
                'message' => 'Welcome email sent successfully'
            ];

        } catch (\Exception $e) {
            Log::error('Failed to send welcome email', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate welcome email content using AI
     */
    private function generateWelcomeEmailContent(User $user): array
    {
        $prompt = $this->buildWelcomeEmailPrompt($user);
        
        $messages = [
            ['role' => 'user', 'content' => $prompt]
        ];

        $response = $this->openAIService->chat($messages);

        if (!$response['success']) {
            return $response;
        }

        // Parse AI response to extract subject and body
        $content = $response['message'];
        $parts = explode("\n\n", $content, 2);
        
        $subject = str_replace('Subject: ', '', $parts[0]);
        $body = $parts[1] ?? $content;

        return [
            'success' => true,
            'subject' => $subject,
            'body' => $body
        ];
    }

    /**
     * Build welcome email prompt for AI
     */
    private function buildWelcomeEmailPrompt(User $user): string
    {
        $userInfo = [
            'name' => $user->name,
            'email' => $user->email,
            'gender' => $user->profile->gender ?? 'not specified',
            'age' => $user->profile->age ?? 'not specified',
            'location' => $user->profile->location ?? 'not specified'
        ];

        return "Generate a warm, personalized welcome email for a new ZawajAfrica user.

User Information:
- Name: {$userInfo['name']}
- Gender: {$userInfo['gender']}
- Age: {$userInfo['age']}
- Location: {$userInfo['location']}

Requirements:
1. Start with 'Subject: ' followed by an engaging subject line
2. Write in a warm, professional tone
3. Welcome them to the ZawajAfrica community
4. Mention our Islamic values and African heritage
5. Encourage them to complete their profile
6. Mention our therapist services if appropriate
7. Include next steps they should take
8. Keep it under 300 words
9. End with Islamic greeting (Barakallahu feeki/feek based on gender)

Format:
Subject: [subject line]

[email body]";
    }

    /**
     * Generate weekly admin report
     */
    public function generateWeeklyReport(): array
    {
        try {
            $reportData = $this->gatherWeeklyData();
            $reportContent = $this->generateReportContent($reportData);
            
            if (!$reportContent['success']) {
                return $reportContent;
            }

            // Send to admin email using admin sender
            $this->zohoMailService->configureMailerWithSender('admin');
            $adminEmail = config('services.zoho_mail.addresses.admin.address', config('services.zoho_mail.from_address'));
            $this->sendEmail(
                $adminEmail,
                $reportContent['subject'],
                $reportContent['body'],
                'ZawajAfrica Admin'
            );

            Log::info('Weekly AI report sent successfully');

            return [
                'success' => true,
                'message' => 'Weekly report sent successfully'
            ];

        } catch (\Exception $e) {
            Log::error('Failed to generate weekly report', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Gather data for weekly report
     */
    private function gatherWeeklyData(): array
    {
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();

        return [
            'new_users' => User::whereBetween('created_at', [$startOfWeek, $endOfWeek])->count(),
            'total_users' => User::count(),
            'chatbot_conversations' => ChatbotConversation::whereBetween('created_at', [$startOfWeek, $endOfWeek])->count(),
            'active_users' => User::whereBetween('last_login_at', [$startOfWeek, $endOfWeek])->count(),
            'therapist_bookings' => \App\Models\TherapistBooking::whereBetween('created_at', [$startOfWeek, $endOfWeek])->count(),
            'new_matches' => \App\Models\UserMatch::whereBetween('matched_at', [$startOfWeek, $endOfWeek])->count(),
            'week_period' => $startOfWeek->format('M j') . ' - ' . $endOfWeek->format('M j, Y')
        ];
    }

    /**
     * Generate report content using AI
     */
    private function generateReportContent(array $data): array
    {
        $prompt = "Generate a professional weekly report for ZawajAfrica platform admins.

Data for this week ({$data['week_period']}):
- New users: {$data['new_users']}
- Total users: {$data['total_users']}
- AI chatbot conversations: {$data['chatbot_conversations']}
- Active users: {$data['active_users']}
- Therapist bookings: {$data['therapist_bookings']}
- New matches created: {$data['new_matches']}

Please provide:
1. Subject line starting with 'Subject: '
2. Executive summary
3. Key metrics analysis
4. Insights and trends
5. Recommendations for next week
6. Keep it professional and actionable

Format:
Subject: [subject line]

[report body]";

        $messages = [
            ['role' => 'user', 'content' => $prompt]
        ];

        $response = $this->openAIService->chat($messages);

        if (!$response['success']) {
            return $response;
        }

        $content = $response['message'];
        $parts = explode("\n\n", $content, 2);
        
        $subject = str_replace('Subject: ', '', $parts[0]);
        $body = $parts[1] ?? $content;

        return [
            'success' => true,
            'subject' => $subject,
            'body' => $body
        ];
    }

    /**
     * Generate personalized match suggestions email
     */
    public function sendMatchSuggestionsEmail(User $user): array
    {
        try {
            // Get potential matches for user
            $matches = $this->findPotentialMatches($user);
            
            if (empty($matches)) {
                return [
                    'success' => false,
                    'message' => 'No matches found for user'
                ];
            }

            $emailContent = $this->generateMatchEmailContent($user, $matches);
            
            if (!$emailContent['success']) {
                return $emailContent;
            }

            // Use support email for user-facing match suggestions
            $this->zohoMailService->configureMailerWithSender('support');
            $this->sendEmail(
                $user->email,
                $emailContent['subject'],
                $emailContent['body'],
                $user->name
            );

            Log::info('AI match suggestions email sent', ['user_id' => $user->id]);

            return [
                'success' => true,
                'message' => 'Match suggestions email sent successfully'
            ];

        } catch (\Exception $e) {
            Log::error('Failed to send match suggestions email', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Find potential matches for user (simplified logic)
     */
    private function findPotentialMatches(User $user): array
    {
        // Basic matching logic - you can enhance this
        $oppositeGender = $user->profile->gender === 'male' ? 'female' : 'male';
        
        return User::whereHas('profile', function($query) use ($oppositeGender) {
                $query->where('gender', $oppositeGender);
            })
            ->where('id', '!=', $user->id)
            ->with(['profile', 'about'])
            ->limit(3)
            ->get()
            ->toArray();
    }

    /**
     * Generate match suggestions email content
     */
    private function generateMatchEmailContent(User $user, array $matches): array
    {
        $matchDescriptions = [];
        foreach ($matches as $match) {
            $matchDescriptions[] = "- {$match['name']}, {$match['profile']['age']}, {$match['profile']['location']}";
        }

        $prompt = "Generate a personalized email suggesting potential matches for a ZawajAfrica user.

User: {$user->name}
Suggested Matches:
" . implode("\n", $matchDescriptions) . "

Requirements:
1. Start with 'Subject: ' followed by engaging subject line
2. Personalized greeting
3. Mention the suggested matches positively
4. Encourage them to log in and connect
5. Maintain Islamic values and respectful tone
6. Include call-to-action to visit the platform
7. Keep under 250 words

Format:
Subject: [subject line]

[email body]";

        $messages = [
            ['role' => 'user', 'content' => $prompt]
        ];

        $response = $this->openAIService->chat($messages);

        if (!$response['success']) {
            return $response;
        }

        $content = $response['message'];
        $parts = explode("\n\n", $content, 2);
        
        $subject = str_replace('Subject: ', '', $parts[0]);
        $body = $parts[1] ?? $content;

        return [
            'success' => true,
            'subject' => $subject,
            'body' => $body
        ];
    }

    /**
     * Send email using Zoho Mail service
     */
    private function sendEmail(string $to, string $subject, string $body, string $toName = ''): void
    {
        $this->zohoMailService->configureMailer();

        Mail::raw($body, function ($message) use ($to, $subject, $toName) {
            $message->to($to, $toName)
                   ->subject($subject);
        });
    }


} 
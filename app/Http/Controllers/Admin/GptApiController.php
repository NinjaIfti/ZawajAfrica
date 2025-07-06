<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\OpenAIService;
use App\Services\SeoAuditService;
use App\Services\AIEmailService;
use App\Services\NotificationService;
use App\Models\User;
use App\Models\UserReport;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Inertia\Inertia;

class GptApiController extends Controller
{
    protected OpenAIService $openAIService;
    protected SeoAuditService $seoAuditService;
    protected AIEmailService $aiEmailService;
    protected NotificationService $notificationService;

    public function __construct(
        OpenAIService $openAIService,
        SeoAuditService $seoAuditService,
        AIEmailService $aiEmailService,
        NotificationService $notificationService
    ) {
        $this->openAIService = $openAIService;
        $this->seoAuditService = $seoAuditService;
        $this->aiEmailService = $aiEmailService;
        $this->notificationService = $notificationService;
    }

    /**
     * Get available commands
     */
    public function getAvailableCommands(): JsonResponse
    {
        $commands = [
            [
                'command' => 'send_welcome_to_new_users',
                'description' => 'Send welcome emails to new users',
                'parameters' => [
                    'hours' => ['type' => 'integer', 'default' => 24, 'description' => 'Hours to look back for new users'],
                    'limit' => ['type' => 'integer', 'default' => 50, 'description' => 'Maximum number of emails to send']
                ]
            ],
            [
                'command' => 'check_site_issues',
                'description' => 'Check for site issues and health',
                'parameters' => []
            ],
            [
                'command' => 'run_seo_audit',
                'description' => 'Run comprehensive SEO audit',
                'parameters' => [
                    'url' => ['type' => 'string', 'default' => 'site_url', 'description' => 'URL to audit'],
                    'type' => ['type' => 'string', 'default' => 'full', 'description' => 'Audit type: basic, technical, content, full']
                ]
            ],
            [
                'command' => 'broadcast_alert',
                'description' => 'Send broadcast message to users',
                'parameters' => [
                    'message' => ['type' => 'string', 'required' => true, 'description' => 'Message to broadcast'],
                    'audience' => ['type' => 'string', 'default' => 'all', 'description' => 'Target audience: all, premium, free, verified, unverified'],
                    'priority' => ['type' => 'string', 'default' => 'normal', 'description' => 'Message priority: low, normal, high, urgent']
                ]
            ],
            [
                'command' => 'get_user_report',
                'description' => 'Generate user activity report',
                'parameters' => [
                    'user_id' => ['type' => 'integer', 'description' => 'Specific user ID (optional)'],
                    'time_range' => ['type' => 'string', 'default' => '7d', 'description' => 'Time range: 1d, 7d, 30d, 90d'],
                    'type' => ['type' => 'string', 'default' => 'activity', 'description' => 'Report type: activity, engagement, conversion']
                ]
            ],
            [
                'command' => 'analyze_user_activity',
                'description' => 'Analyze user activity patterns',
                'parameters' => [
                    'time_range' => ['type' => 'string', 'default' => '7d', 'description' => 'Time range: 1d, 7d, 30d, 90d'],
                    'activity_type' => ['type' => 'string', 'default' => 'all', 'description' => 'Activity type: all, profile_views, messages_sent, likes_sent, matches_created']
                ]
            ],
            [
                'command' => 'generate_insights',
                'description' => 'Generate AI-powered insights',
                'parameters' => [
                    'type' => ['type' => 'string', 'default' => 'general', 'description' => 'Insight type: general, user_engagement, conversion, growth']
                ]
            ]
        ];

        return response()->json([
            'success' => true,
            'commands' => $commands
        ]);
    }

    /**
     * Display GPT integration page
     */
    public function gptIntegrationPage()
    {
        return Inertia::render('Admin/GptIntegration');
    }

    /**
     * Run SEO audit directly
     */
    public function runSeoAudit(Request $request): JsonResponse
    {
        if (!$this->validateAdminAccess($request)) {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        $request->validate([
            'url' => 'required|url',
            'type' => 'string|in:basic,technical,content,full'
        ]);

        try {
            // Check if SeoAuditService is available
            if (!$this->seoAuditService) {
                throw new \Exception('SEO Audit Service is not available');
            }

            $url = $request->input('url');
            $type = $request->input('type', 'full');

            Log::info('Starting SEO audit', [
                'url' => $url,
                'type' => $type,
                'admin_id' => auth()->id()
            ]);

            $result = $this->seoAuditService->runAudit($url, $type);

            Log::info('SEO audit completed successfully', [
                'url' => $url,
                'type' => $type,
                'success' => $result['success'] ?? false
            ]);

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('SEO audit failed', [
                'url' => $request->input('url'),
                'type' => $request->input('type'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'admin_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'SEO audit failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle GPT commands from admin panel
     */
    public function handleCommand(Request $request): JsonResponse
    {
        // Validate admin access
        if (!$this->validateAdminAccess($request)) {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        $request->validate([
            'command' => 'required|string',
            'parameters' => 'array'
        ]);

        $command = $request->input('command');
        $parameters = $request->input('parameters', []);

        try {
            $result = $this->executeCommand($command, $parameters);
            
            Log::info('GPT command executed successfully', [
                'command' => $command,
                'parameters' => $parameters,
                'admin_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'data' => $result,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error('GPT command execution failed', [
                'command' => $command,
                'parameters' => $parameters,
                'error' => $e->getMessage(),
                'admin_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Execute specific commands
     */
    private function executeCommand(string $command, array $parameters): array
    {
        switch ($command) {
            case 'send_welcome_to_new_users':
                return $this->sendWelcomeToNewUsers($parameters);
            
            case 'check_site_issues':
                return $this->checkSiteIssues($parameters);
            
            case 'run_seo_audit':
                return $this->executeSeoAudit($parameters);
            
            case 'broadcast_alert':
                return $this->broadcastAlert($parameters);
            
            case 'get_user_report':
                return $this->getUserReport($parameters);
            
            case 'analyze_user_activity':
                return $this->analyzeUserActivity($parameters);
            
            case 'generate_insights':
                return $this->generateInsights($parameters);
            
            default:
                throw new \InvalidArgumentException("Unknown command: {$command}");
        }
    }

    /**
     * Send welcome emails to new users
     */
    private function sendWelcomeToNewUsers(array $parameters): array
    {
        $hours = $parameters['hours'] ?? 24;
        $limit = $parameters['limit'] ?? 50;

        $newUsers = User::where('created_at', '>=', now()->subHours($hours))
            ->where('email_verified_at', '!=', null)
            ->limit($limit)
            ->get();

        $sentCount = 0;
        $errors = [];

        foreach ($newUsers as $user) {
            try {
                $this->aiEmailService->sendWelcomeEmail($user);
                $sentCount++;
            } catch (\Exception $e) {
                $errors[] = "Failed to send welcome email to {$user->email}: " . $e->getMessage();
            }
        }

        return [
            'command' => 'send_welcome_to_new_users',
            'users_found' => $newUsers->count(),
            'emails_sent' => $sentCount,
            'errors' => $errors,
            'time_range' => "Last {$hours} hours"
        ];
    }

    /**
     * Check for site issues
     */
    private function checkSiteIssues(array $parameters): array
    {
        $issues = [];

        // Check database connectivity
        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            $issues[] = [
                'type' => 'database',
                'severity' => 'critical',
                'message' => 'Database connection failed: ' . $e->getMessage()
            ];
        }

        // Check pending verifications
        $pendingVerifications = User::whereHas('verification', function($query) {
            $query->where('status', 'pending');
        })->count();

        if ($pendingVerifications > 100) {
            $issues[] = [
                'type' => 'verification',
                'severity' => 'warning',
                'message' => "High number of pending verifications: {$pendingVerifications}"
            ];
        }

        // Check failed jobs
        $failedJobs = DB::table('failed_jobs')->count();
        if ($failedJobs > 10) {
            $issues[] = [
                'type' => 'jobs',
                'severity' => 'warning',
                'message' => "High number of failed jobs: {$failedJobs}"
            ];
        }

        // Check user activity
        $inactiveUsers = User::where('last_activity_at', '<', now()->subDays(30))->count();
        if ($inactiveUsers > 1000) {
            $issues[] = [
                'type' => 'activity',
                'severity' => 'info',
                'message' => "Many inactive users: {$inactiveUsers}"
            ];
        }

        return [
            'command' => 'check_site_issues',
            'issues_found' => count($issues),
            'issues' => $issues,
            'timestamp' => now()->toISOString()
        ];
    }

    /**
     * Run SEO audit
     */
    private function executeSeoAudit(array $parameters): array
    {
        $url = $parameters['url'] ?? config('app.url');
        $auditType = $parameters['type'] ?? 'full';

        try {
            $auditResult = $this->seoAuditService->runAudit($url, $auditType);
            
            // Generate AI summary of audit results
            $summary = $this->generateAuditSummary($auditResult);
            
            return [
                'command' => 'run_seo_audit',
                'url' => $url,
                'audit_type' => $auditType,
                'audit_result' => $auditResult,
                'ai_summary' => $summary,
                'timestamp' => now()->toISOString()
            ];

        } catch (\Exception $e) {
            throw new \Exception("SEO audit failed: " . $e->getMessage());
        }
    }

    /**
     * Broadcast alert to users
     */
    private function broadcastAlert(array $parameters): array
    {
        $message = $parameters['message'] ?? '';
        $audience = $parameters['audience'] ?? 'all';
        $priority = $parameters['priority'] ?? 'normal';

        if (empty($message)) {
            throw new \InvalidArgumentException('Message is required for broadcast');
        }

        $users = $this->getUsersByAudience($audience);
        $sentCount = 0;
        $errors = [];

        foreach ($users as $user) {
            try {
                $this->notificationService->sendBroadcastNotification($user, $message, $priority);
                $sentCount++;
            } catch (\Exception $e) {
                $errors[] = "Failed to send to {$user->email}: " . $e->getMessage();
            }
        }

        return [
            'command' => 'broadcast_alert',
            'message' => $message,
            'audience' => $audience,
            'priority' => $priority,
            'users_targeted' => $users->count(),
            'messages_sent' => $sentCount,
            'errors' => $errors
        ];
    }

    /**
     * Get user report
     */
    private function getUserReport(array $parameters): array
    {
        $userId = $parameters['user_id'] ?? null;
        $timeRange = $parameters['time_range'] ?? '7d';
        $reportType = $parameters['type'] ?? 'activity';

        if ($userId) {
            $user = User::find($userId);
            if (!$user) {
                throw new \InvalidArgumentException("User with ID {$userId} not found");
            }
            return $this->generateUserReport($user, $timeRange, $reportType);
        } else {
            return $this->generateSystemReport($timeRange, $reportType);
        }
    }

    /**
     * Analyze user activity
     */
    private function analyzeUserActivity(array $parameters): array
    {
        $timeRange = $parameters['time_range'] ?? '7d';
        $activityType = $parameters['activity_type'] ?? 'all';

        $dateFilter = $this->getDateFilter($timeRange);
        
        $query = DB::table('user_daily_activities')
            ->where('date', '>=', $dateFilter);

        if ($activityType !== 'all') {
            $query->where('activity', $activityType);
        }

        $activities = $query->get();

        $analysis = [
            'total_activities' => $activities->sum('count'),
            'unique_users' => $activities->distinct('user_id')->count(),
            'activity_breakdown' => $activities->groupBy('activity')
                ->map(function ($group) {
                    return $group->sum('count');
                }),
            'daily_trends' => $activities->groupBy('date')
                ->map(function ($group) {
                    return $group->sum('count');
                })
        ];

        return [
            'command' => 'analyze_user_activity',
            'time_range' => $timeRange,
            'activity_type' => $activityType,
            'analysis' => $analysis
        ];
    }

    /**
     * Generate insights
     */
    private function generateInsights(array $parameters): array
    {
        $insightType = $parameters['type'] ?? 'general';
        $data = $this->gatherAnalyticsData();
        
        $prompt = $this->buildInsightPrompt($insightType, $data);
        
        $messages = [
            ['role' => 'user', 'content' => $prompt]
        ];

        $response = $this->openAIService->chat($messages);

        if (!$response['success']) {
            throw new \Exception('Failed to generate insights: ' . $response['error']);
        }

        return [
            'command' => 'generate_insights',
            'type' => $insightType,
            'insights' => $response['message'],
            'data_used' => $data
        ];
    }

    /**
     * Helper methods
     */
    private function validateAdminAccess(Request $request): bool
    {
        return $request->user() && $request->user()->email === 'admin@zawagafrica.com';
    }



    private function getUsersByAudience(string $audience)
    {
        switch ($audience) {
            case 'all':
                return User::all();
            case 'premium':
                return User::whereNotNull('subscription_plan')
                    ->where('subscription_status', 'active')
                    ->get();
            case 'free':
                return User::where(function($query) {
                    $query->whereNull('subscription_plan')
                          ->orWhere('subscription_status', '!=', 'active');
                })->get();
            case 'verified':
                return User::where('is_verified', true)->get();
            case 'unverified':
                return User::where('is_verified', false)->get();
            default:
                return collect();
        }
    }

    private function generateAuditSummary(array $auditResult): string
    {
        $prompt = "Analyze this SEO audit result and provide a concise summary with actionable recommendations:\n\n" . json_encode($auditResult);
        return $this->openAIService->generateSummary($prompt);
    }

    private function generateUserReport(User $user, string $timeRange, string $reportType): array
    {
        $dateFilter = $this->getDateFilter($timeRange);
        
        $activities = DB::table('user_daily_activities')
            ->where('user_id', $user->id)
            ->where('date', '>=', $dateFilter)
            ->get();

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at,
                'is_verified' => $user->is_verified,
                'subscription_plan' => $user->subscription_plan
            ],
            'activities' => $activities,
            'time_range' => $timeRange,
            'report_type' => $reportType
        ];
    }

    private function generateSystemReport(string $timeRange, string $reportType): array
    {
        $dateFilter = $this->getDateFilter($timeRange);
        
        $stats = [
            'total_users' => User::count(),
            'new_users' => User::where('created_at', '>=', $dateFilter)->count(),
            'active_users' => DB::table('user_daily_activities')
                ->where('date', '>=', $dateFilter)
                ->distinct('user_id')
                ->count(),
            'total_activities' => DB::table('user_daily_activities')
                ->where('date', '>=', $dateFilter)
                ->sum('count')
        ];

        return [
            'system_stats' => $stats,
            'time_range' => $timeRange,
            'report_type' => $reportType
        ];
    }

    private function getDateFilter(string $timeRange): string
    {
        switch ($timeRange) {
            case '1d':
                return now()->subDay()->format('Y-m-d');
            case '7d':
                return now()->subWeek()->format('Y-m-d');
            case '30d':
                return now()->subMonth()->format('Y-m-d');
            case '90d':
                return now()->subMonths(3)->format('Y-m-d');
            default:
                return now()->subWeek()->format('Y-m-d');
        }
    }

    private function gatherAnalyticsData(): array
    {
        return [
            'total_users' => User::count(),
            'verified_users' => User::where('is_verified', true)->count(),
            'premium_users' => User::whereNotNull('subscription_plan')
                ->where('subscription_status', 'active')
                ->count(),
            'recent_activity' => DB::table('user_daily_activities')
                ->where('date', '>=', now()->subDays(7)->format('Y-m-d'))
                ->sum('count')
        ];
    }

    private function buildInsightPrompt(string $type, array $data): string
    {
        $basePrompt = "Analyze the following data and provide insights for a Muslim matrimonial platform:\n\n" . json_encode($data);
        
        switch ($type) {
            case 'user_engagement':
                return $basePrompt . "\n\nFocus on user engagement patterns and recommendations to improve user activity.";
            case 'conversion':
                return $basePrompt . "\n\nFocus on conversion rates and strategies to increase premium subscriptions.";
            case 'growth':
                return $basePrompt . "\n\nFocus on user growth patterns and strategies to attract more users.";
            default:
                return $basePrompt . "\n\nProvide general insights and recommendations.";
        }
    }
} 
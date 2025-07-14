<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AdsterraService
{
    protected UserTierService $tierService;
    protected array $config;

    public function __construct(UserTierService $tierService)
    {
        $this->tierService = $tierService;
        $this->config = config('adsterra');
        
        // Validate configuration on instantiation
        $this->validateConfiguration();
    }

    /**
     * Validate Adsterra configuration
     */
    protected function validateConfiguration(): void
    {
        if (!$this->config['enabled']) {
            return;
        }

        $required = ['script_url', 'publisher_id'];
        
        foreach ($required as $key) {
            if (empty($this->config[$key])) {
                Log::error("Adsterra configuration missing required field: {$key}");
                throw new \InvalidArgumentException("Adsterra configuration incomplete: {$key} is required");
            }
        }

        // Validate script URL format
        if (!$this->isValidScriptUrl($this->config['script_url'])) {
            Log::error('Invalid Adsterra script URL format', ['url' => $this->config['script_url']]);
            throw new \InvalidArgumentException('Invalid Adsterra script URL format');
        }

        // Validate publisher ID format
        if (!$this->isValidPublisherId($this->config['publisher_id'])) {
            Log::error('Invalid Adsterra publisher ID format', ['id' => $this->config['publisher_id']]);
            throw new \InvalidArgumentException('Invalid Adsterra publisher ID format');
        }
    }

    /**
     * Validate script URL format
     */
    protected function isValidScriptUrl(string $url): bool
    {
        // TEMPORARILY DISABLE STRICT VALIDATION FOR TESTING
        if ($this->config['debug']['enabled']) {
            return !empty($url); // Just check it's not empty in debug mode
        }
        
        // Check if it's a valid URL format
        if (!filter_var('https:' . $url, FILTER_VALIDATE_URL)) {
            return false;
        }

        // Check if it's from allowed domains
        $allowedDomains = $this->config['security']['allowed_domains'] ?? [];
        foreach ($allowedDomains as $domain) {
            if (str_contains($url, $domain)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validate publisher ID format
     */
    protected function isValidPublisherId(string $id): bool
    {
        // TEMPORARILY DISABLE STRICT VALIDATION FOR TESTING
        if ($this->config['debug']['enabled']) {
            return !empty($id); // Just check it's not empty in debug mode
        }
        
        // Adsterra publisher IDs are typically 32-character hex strings
        return preg_match('/^[a-f0-9]{32}$/', $id) === 1;
    }

    /**
     * Check if ads should be displayed for the current user
     */
    public function shouldShowAds(?User $user = null): bool
    {
        try {
            // If Adsterra is disabled globally
            if (!$this->config['enabled']) {
                $this->logDebug('Ads disabled globally');
                return false;
            }

            // Check rate limiting
            if (!$this->checkRateLimit($user)) {
                $this->logDebug('Rate limit exceeded', ['user_id' => $user?->id]);
                return false;
            }

            // If no user (guest), show ads
            if (!$user) {
                $this->logDebug('Guest user - showing ads');
                return true;
            }

            // Check if user has active paid subscription - paid users don't see ads
            $userTier = $this->tierService->getUserTier($user);
            if ($userTier !== 'free') {
                $this->logDebug('Paid user - no ads', [
                    'user_id' => $user->id, 
                    'tier' => $userTier,
                    'subscription_status' => $user->subscription_status,
                    'subscription_plan' => $user->subscription_plan
                ]);
                return false;
            }

            // For free users, use tier service logic for ad frequency
            // TEMPORARILY BYPASS FREQUENCY CHECK FOR TESTING
            if ($this->config['debug']['enabled']) {
                $this->logDebug('Free user - DEBUG MODE: bypassing frequency check', [
                    'user_id' => $user->id, 
                    'tier' => $userTier
                ]);
                return true; // Always show ads for free users in debug mode
            }
            
            $shouldShow = $this->tierService->shouldShowAds($user);
            $this->logDebug('Free user - tier service decision', [
                'user_id' => $user->id, 
                'show_ads' => $shouldShow,
                'tier' => $userTier
            ]);
            
            return $shouldShow;
        } catch (\Exception $e) {
            $this->logError('Error checking if ads should show', ['error' => $e->getMessage()]);
            return false; // Fail safely
        }
    }

    /**
     * Check if current page should show ads
     */
    public function shouldShowAdsOnPage(Request $request): bool
    {
        try {
            $restrictedPages = $this->config['restricted_pages'] ?? [];
            $currentPath = $request->path();

            foreach ($restrictedPages as $restrictedPage) {
                if (str_contains($currentPath, $restrictedPage)) {
                    $this->logDebug('Page restricted for ads', ['path' => $currentPath, 'restriction' => $restrictedPage]);
                    return false;
                }
            }

            // Check device type restrictions
            if (!$this->isDeviceAllowed($request)) {
                $this->logDebug('Device type not allowed', ['user_agent' => $request->userAgent()]);
                return false;
            }

            // Check country restrictions
            $country = $this->getCountryFromRequest($request);
            if (!$this->isCountryAllowed($country)) {
                $this->logDebug('Country not allowed', ['country' => $country]);
                return false;
            }

            return true;
        } catch (\Exception $e) {
            $this->logError('Error checking page restrictions', ['error' => $e->getMessage()]);
            return false; // Fail safely
        }
    }

    /**
     * Check rate limiting for ad requests
     */
    protected function checkRateLimit(?User $user): bool
    {
        if (!$this->config['security']['rate_limiting']) {
            return true;
        }

        $key = 'adsterra_rate_limit_' . ($user ? $user->id : request()->ip());
        $maxRequests = $this->config['security']['max_requests_per_minute'] ?? 60;
        
        $currentCount = Cache::get($key, 0);
        
        if ($currentCount >= $maxRequests) {
            return false;
        }

        Cache::put($key, $currentCount + 1, 60); // 1 minute TTL
        return true;
    }

    /**
     * Check if device type is allowed
     */
    protected function isDeviceAllowed(Request $request): bool
    {
        $allowedDevices = $this->config['targeting']['allowed_device_types'] ?? [];
        
        if (empty($allowedDevices)) {
            return true; // No restrictions
        }

        $userAgent = $request->userAgent();
        
        if (in_array('mobile', $allowedDevices) && $this->isMobile($userAgent)) {
            return true;
        }
        
        if (in_array('tablet', $allowedDevices) && $this->isTablet($userAgent)) {
            return true;
        }
        
        if (in_array('desktop', $allowedDevices) && $this->isDesktop($userAgent)) {
            return true;
        }

        return false;
    }

    /**
     * Get country from request
     */
    protected function getCountryFromRequest(Request $request): ?string
    {
        // Try to get country from user if authenticated
        if ($user = $request->user()) {
            return $user->country ?? null;
        }

        // Try to get from IP geolocation (basic implementation)
        // In production, you might use a service like MaxMind or similar
        return null;
    }

    /**
     * Device detection helper methods
     */
    protected function isMobile(string $userAgent): bool
    {
        return preg_match('/Mobile|Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i', $userAgent);
    }

    protected function isTablet(string $userAgent): bool
    {
        return preg_match('/iPad|Android.*Tablet|Kindle|Silk/i', $userAgent);
    }

    protected function isDesktop(string $userAgent): bool
    {
        return !$this->isMobile($userAgent) && !$this->isTablet($userAgent);
    }

    /**
     * Logging helper methods
     */
    protected function logDebug(string $message, array $context = []): void
    {
        if ($this->config['debug']['enabled']) {
            Log::debug("Adsterra: {$message}", $context);
        }
    }

    protected function logError(string $message, array $context = []): void
    {
        if ($this->config['analytics']['error_tracking']) {
            Log::error("Adsterra: {$message}", $context);
        }
    }

    protected function logInfo(string $message, array $context = []): void
    {
        if ($this->config['analytics']['tracking_enabled']) {
            Log::info("Adsterra: {$message}", $context);
        }
    }

    /**
     * Get Adsterra configuration for frontend
     */
    public function getAdsterraConfig(?User $user = null): array
    {
        try {
            if (!$this->shouldShowAds($user)) {
                return [
                    'enabled' => false,
                    'reason' => $user ? 'paid_user' : 'disabled'
                ];
            }

            $config = [
                'enabled' => true,
                'script_url' => $this->config['script_url'],
                'publisher_id' => $this->config['publisher_id'],
                'display' => $this->config['display'],
                'ad_zones' => $this->filterAdZones(),
                'privacy' => $this->getPrivacyConfig(),
                'debug' => $this->config['debug']['enabled'],
                'test_mode' => $this->config['test_mode'],
                'performance' => [
                    'timeout' => $this->config['performance']['timeout'],
                    'async_loading' => $this->config['performance']['async_loading'],
                    'lazy_loading' => $this->config['display']['lazy_loading'],
                    'retry_attempts' => $this->config['performance']['retry_attempts'],
                    'retry_delay' => $this->config['performance']['retry_delay'],
                ],
                'fallback' => $this->config['fallback'],
                'quality' => $this->config['quality'],
            ];

            // Add CSP information if enabled
            if ($this->config['security']['csp_enabled']) {
                $config['security'] = [
                    'csp_enabled' => true,
                    'allowed_domains' => $this->config['security']['allowed_domains'],
                ];
            }

            return $config;
        } catch (\Exception $e) {
            $this->logError('Error getting Adsterra config', ['error' => $e->getMessage()]);
            return [
                'enabled' => false,
                'reason' => 'configuration_error'
            ];
        }
    }

    /**
     * Filter and validate ad zones
     */
    protected function filterAdZones(): array
    {
        $zones = $this->config['ad_zones'] ?? [];
        $filtered = [];

        foreach ($zones as $type => $zoneId) {
            if (!empty($zoneId) && $this->isValidZoneId($zoneId)) {
                $filtered[$type] = $zoneId;
            }
        }

        return $filtered;
    }

    /**
     * Validate ad zone ID
     */
    protected function isValidZoneId(string $zoneId): bool
    {
        // Basic validation - zone IDs should be alphanumeric
        return preg_match('/^[a-zA-Z0-9]+$/', $zoneId) === 1;
    }

    /**
     * Get privacy configuration
     */
    protected function getPrivacyConfig(): array
    {
        return [
            'gdpr_enabled' => $this->config['privacy']['gdpr_enabled'],
            'cookie_consent' => $this->config['privacy']['cookie_consent'],
        ];
    }

    /**
     * Check if user's country is allowed for ads
     */
    public function isCountryAllowed(?string $country = null): bool
    {
        $allowedCountries = $this->config['targeting']['allowed_countries'];
        $blockedCountries = $this->config['targeting']['blocked_countries'];

        // If no country provided, allow by default
        if (!$country) {
            return true;
        }

        // Check if country is blocked
        if (in_array($country, $blockedCountries)) {
            return false;
        }

        // If allowed countries list is empty, allow all (except blocked)
        if (empty($allowedCountries)) {
            return true;
        }

        // Check if country is in allowed list
        return in_array($country, $allowedCountries);
    }

    /**
     * Generate Adsterra script with security measures
     */
    public function generateAdsterraScript(): string
    {
        try {
            $scriptUrl = $this->config['script_url'];

            if (!$scriptUrl) {
                $this->logError('Adsterra Script URL not configured');
                return $this->generateFallbackScript();
            }

            // Validate script URL
            if (!$this->isValidScriptUrl($scriptUrl)) {
                $this->logError('Invalid Adsterra Script URL', ['url' => $scriptUrl]);
                return $this->generateFallbackScript();
            }

            // Generate script with security attributes
            $attributes = [
                'type' => 'text/javascript',
                'src' => $scriptUrl,
                'async' => $this->config['performance']['async_loading'] ? 'true' : 'false',
                'defer' => 'true',
                'data-adsterra-publisher' => $this->config['publisher_id'],
                'data-adsterra-timestamp' => time(),
            ];

            // Add integrity check if available
            if ($this->config['security']['validate_scripts']) {
                $attributes['crossorigin'] = 'anonymous';
            }

            // Add CSP nonce if available
            if ($nonce = $this->getCSPNonce()) {
                $attributes['nonce'] = $nonce;
            }

            $attributeString = '';
            foreach ($attributes as $key => $value) {
                $attributeString .= " {$key}=\"{$value}\"";
            }

            $script = "<script{$attributeString}></script>";

            // Add error handling script
            if ($this->config['performance']['retry_attempts'] > 0) {
                $script .= $this->generateRetryScript();
            }

            $this->logInfo('Adsterra script generated successfully');
            return $script;
            
        } catch (\Exception $e) {
            $this->logError('Error generating Adsterra script', ['error' => $e->getMessage()]);
            return $this->generateFallbackScript();
        }
    }

    /**
     * Generate fallback script for error cases
     */
    protected function generateFallbackScript(): string
    {
        if (!$this->config['fallback']['enabled']) {
            return '';
        }

        if ($this->config['fallback']['show_fallback']) {
            return '<div class="adsterra-fallback">' . 
                   htmlspecialchars($this->config['fallback']['fallback_message']) . 
                   '</div>';
        }

        return '';
    }

    /**
     * Generate retry script for failed loads
     */
    protected function generateRetryScript(): string
    {
        $retryAttempts = $this->config['performance']['retry_attempts'];
        $retryDelay = $this->config['performance']['retry_delay'];
        $scriptUrl = $this->config['script_url'];

        return "
        <script type='text/javascript'>
            (function() {
                let retryCount = 0;
                const maxRetries = {$retryAttempts};
                const retryDelay = {$retryDelay};
                
                function loadAdsterraScript() {
                    if (retryCount >= maxRetries) {
                        console.warn('Adsterra: Maximum retry attempts reached');
                        return;
                    }
                    
                    const script = document.createElement('script');
                    script.type = 'text/javascript';
                    script.src = '{$scriptUrl}';
                    script.async = true;
                    script.defer = true;
                    
                    script.onerror = function() {
                        retryCount++;
                        console.warn('Adsterra: Script load failed, retrying...', retryCount);
                        setTimeout(loadAdsterraScript, retryDelay);
                    };
                    
                    script.onload = function() {
                        console.log('Adsterra: Script loaded successfully');
                    };
                    
                    document.head.appendChild(script);
                }
                
                // Initial load attempt
                loadAdsterraScript();
            })();
        </script>";
    }

    /**
     * Get CSP nonce if available
     */
    protected function getCSPNonce(): ?string
    {
        // This would typically be set by your CSP middleware
        // For now, return null - implement based on your CSP setup
        return null;
    }

    /**
     * Log ad impression for analytics with enhanced tracking
     */
    public function logAdImpression(User $user, string $adType = 'banner', array $metadata = []): void
    {
        if (!$this->config['analytics']['impressions_tracking']) {
            return;
        }

        try {
            $impressionData = [
                'user_id' => $user->id,
                'user_tier' => $this->tierService->getUserTier($user),
                'ad_type' => $adType,
                'timestamp' => now()->toISOString(),
                'session_id' => session()->getId(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'referrer' => request()->header('referer'),
                'page_url' => request()->fullUrl(),
                'device_type' => $this->getDeviceType(request()->userAgent()),
                'metadata' => $metadata
            ];

            // Log to Laravel logs
            $this->logInfo('Ad impression recorded', $impressionData);

            // Update daily activity tracking
            $this->tierService->recordActivity($user, 'ads_viewed');

            // Cache for reporting with multiple keys
            $this->cacheImpressionData($user, $adType, $impressionData);

            // Track performance metrics
            $this->trackPerformanceMetrics($adType, $metadata);

        } catch (\Exception $e) {
            $this->logError('Failed to log ad impression', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Cache impression data for reporting
     */
    protected function cacheImpressionData(User $user, string $adType, array $impressionData): void
    {
        $today = now()->format('Y-m-d');
        
        // Daily user impressions
        $userKey = "adsterra_impressions_{$user->id}_{$today}";
        $currentCount = Cache::get($userKey, 0);
        Cache::put($userKey, $currentCount + 1, now()->endOfDay());

        // Daily ad type impressions
        $adTypeKey = "adsterra_impressions_{$adType}_{$today}";
        $currentAdTypeCount = Cache::get($adTypeKey, 0);
        Cache::put($adTypeKey, $currentAdTypeCount + 1, now()->endOfDay());

        // Hourly impressions for rate limiting
        $hourlyKey = "adsterra_impressions_hourly_{$user->id}_" . now()->format('Y-m-d-H');
        $currentHourlyCount = Cache::get($hourlyKey, 0);
        Cache::put($hourlyKey, $currentHourlyCount + 1, 3600); // 1 hour TTL

        // Store detailed impression data for analysis
        if ($this->config['analytics']['revenue_tracking']) {
            $detailedKey = "adsterra_detailed_impressions_{$today}";
            $detailedData = Cache::get($detailedKey, []);
            $detailedData[] = $impressionData;
            Cache::put($detailedKey, $detailedData, now()->endOfDay());
        }
    }

    /**
     * Track performance metrics
     */
    protected function trackPerformanceMetrics(string $adType, array $metadata): void
    {
        if (!$this->config['analytics']['performance_tracking']) {
            return;
        }

        try {
            $performanceKey = "adsterra_performance_" . now()->format('Y-m-d');
            $performanceData = Cache::get($performanceKey, [
                'total_impressions' => 0,
                'ad_types' => [],
                'load_times' => [],
                'errors' => 0
            ]);

            $performanceData['total_impressions']++;
            $performanceData['ad_types'][$adType] = ($performanceData['ad_types'][$adType] ?? 0) + 1;

            // Track load time if provided
            if (isset($metadata['load_time'])) {
                $performanceData['load_times'][] = $metadata['load_time'];
            }

            // Track errors if any
            if (isset($metadata['error'])) {
                $performanceData['errors']++;
            }

            Cache::put($performanceKey, $performanceData, now()->endOfDay());

        } catch (\Exception $e) {
            $this->logError('Failed to track performance metrics', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get device type from user agent
     */
    protected function getDeviceType(string $userAgent): string
    {
        if ($this->isMobile($userAgent)) {
            return 'mobile';
        } elseif ($this->isTablet($userAgent)) {
            return 'tablet';
        } else {
            return 'desktop';
        }
    }

    /**
     * Log ad click for analytics
     */
    public function logAdClick(User $user, string $adType = 'banner', array $metadata = []): void
    {
        if (!$this->config['analytics']['click_tracking']) {
            return;
        }

        try {
            $clickData = [
                'user_id' => $user->id,
                'user_tier' => $this->tierService->getUserTier($user),
                'ad_type' => $adType,
                'timestamp' => now()->toISOString(),
                'session_id' => session()->getId(),
                'ip_address' => request()->ip(),
                'metadata' => $metadata
            ];

            $this->logInfo('Ad click recorded', $clickData);

            // Cache click data
            $today = now()->format('Y-m-d');
            $clickKey = "adsterra_clicks_{$user->id}_{$today}";
            $currentClicks = Cache::get($clickKey, 0);
            Cache::put($clickKey, $currentClicks + 1, now()->endOfDay());

        } catch (\Exception $e) {
            $this->logError('Failed to log ad click', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Log ad error for monitoring
     */
    public function logAdError(string $error, array $context = []): void
    {
        if (!$this->config['analytics']['error_tracking']) {
            return;
        }

        try {
            $errorData = [
                'error' => $error,
                'timestamp' => now()->toISOString(),
                'session_id' => session()->getId(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'page_url' => request()->fullUrl(),
                'context' => $context
            ];

            $this->logError('Ad error occurred', $errorData);

            // Cache error data for monitoring
            $today = now()->format('Y-m-d');
            $errorKey = "adsterra_errors_{$today}";
            $currentErrors = Cache::get($errorKey, 0);
            Cache::put($errorKey, $currentErrors + 1, now()->endOfDay());

        } catch (\Exception $e) {
            Log::error('Failed to log ad error', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get ad frequency setting for free users
     */
    public function getAdFrequency(): int
    {
        return $this->config['display']['profile_frequency'];
    }

    /**
     * Check if user should see ad based on their activity count
     */
    public function shouldShowAdAtCount(User $user, int $currentCount): bool
    {
        if (!$this->shouldShowAds($user)) {
            return false;
        }

        $frequency = $this->getAdFrequency();
        return $currentCount > 0 && $currentCount % $frequency === 0;
    }

    /**
     * Get consent status for GDPR compliance
     */
    public function getConsentStatus(Request $request): array
    {
        return [
            'required' => $this->config['privacy']['gdpr_enabled'],
            'cookie_consent' => $request->cookie('adsterra_consent') === 'true',
        ];
    }

    /**
     * Update user consent preferences
     */
    public function updateConsent(Request $request, array $preferences): void
    {
        $expiry = now()->addYear();

        foreach ($preferences as $key => $value) {
            $cookieName = "adsterra_{$key}";
            cookie($cookieName, $value ? 'true' : 'false', $expiry->diffInMinutes());
        }
    }

    /**
     * Get specific ad zone configuration
     */
    public function getAdZone(string $zoneName): ?string
    {
        $zones = $this->config['ad_zones'] ?? [];
        return $zones[$zoneName] ?? null;
    }

    /**
     * Check if specific ad placement is enabled
     */
    public function isAdPlacementEnabled(string $placement): bool
    {
        $display = $this->config['display'] ?? [];
        
        switch ($placement) {
            case 'dashboard_feed':
                return $display['dashboard_feed_enabled'] ?? false;
            case 'sidebar':
                return $display['sidebar_ads_enabled'] ?? false;
            case 'mobile':
                return $display['show_on_mobile'] ?? false;
            default:
                return true;
        }
    }

    /**
     * Generate banner ad HTML
     */
    public function generateBannerAd(string $zoneName = 'banner'): string
    {
        $zoneId = $this->getAdZone($zoneName);
        
        if (!$zoneId) {
            return '';
        }

        return "
        <div class='adsterra-banner' data-zone='{$zoneId}'>
            <!-- Adsterra Banner Ad -->
        </div>";
    }

    /**
     * Generate native ad HTML
     */
    public function generateNativeAd(): string
    {
        $zoneId = $this->getAdZone('native');
        
        if (!$zoneId) {
            return '';
        }

        return "
        <div class='adsterra-native' data-zone='{$zoneId}'>
            <!-- Adsterra Native Ad -->
        </div>";
    }

    /**
     * Log specific ad impression with placement details
     */
    public function logSpecificAdImpression(User $user, string $adType, string $placement, array $metadata = []): void
    {
        $this->logAdImpression($user, $adType, array_merge($metadata, [
            'placement' => $placement,
            'service' => 'adsterra'
        ]));
    }

    /**
     * Get ad performance metrics for reporting
     */
    public function getAdPerformanceMetrics(?string $date = null): array
    {
        $date = $date ?? now()->format('Y-m-d');
        $performanceKey = "adsterra_performance_{$date}";
        
        return Cache::get($performanceKey, [
            'total_impressions' => 0,
            'ad_types' => [],
            'load_times' => [],
            'errors' => 0
        ]);
    }

    /**
     * Health check for Adsterra service
     */
    public function healthCheck(): array
    {
        $health = [
            'status' => 'healthy',
            'checks' => [],
            'timestamp' => now()->toISOString()
        ];

        try {
            // Check configuration
            $health['checks']['configuration'] = $this->config['enabled'] ? 'passed' : 'disabled';

            // Check script URL accessibility
            $health['checks']['script_url'] = $this->isValidScriptUrl($this->config['script_url']) ? 'passed' : 'failed';

            // Check publisher ID format
            $health['checks']['publisher_id'] = $this->isValidPublisherId($this->config['publisher_id']) ? 'passed' : 'failed';

            // Check cache connectivity
            try {
                Cache::put('adsterra_health_check', 'test', 10);
                $health['checks']['cache'] = Cache::get('adsterra_health_check') === 'test' ? 'passed' : 'failed';
                Cache::forget('adsterra_health_check');
            } catch (\Exception $e) {
                $health['checks']['cache'] = 'failed';
            }

            // Check recent error rates
            $today = now()->format('Y-m-d');
            $errors = Cache::get("adsterra_errors_{$today}", 0);
            $impressions = Cache::get("adsterra_impressions_total_{$today}", 0);
            
            $errorRate = $impressions > 0 ? ($errors / $impressions) * 100 : 0;
            $health['checks']['error_rate'] = $errorRate < 5 ? 'passed' : 'warning'; // 5% threshold

            // Overall status
            $failedChecks = array_filter($health['checks'], fn($check) => $check === 'failed');
            if (count($failedChecks) > 0) {
                $health['status'] = 'unhealthy';
            } elseif (in_array('warning', $health['checks'])) {
                $health['status'] = 'degraded';
            }

        } catch (\Exception $e) {
            $health['status'] = 'unhealthy';
            $health['error'] = $e->getMessage();
        }

        return $health;
    }

    /**
     * Clear cached ad data for maintenance
     */
    public function clearAdCache(?string $date = null): void
    {
        $date = $date ?? now()->format('Y-m-d');
        
        $patterns = [
            "adsterra_impressions_*_{$date}",
            "adsterra_clicks_*_{$date}",
            "adsterra_errors_{$date}",
            "adsterra_performance_{$date}",
            "adsterra_detailed_impressions_{$date}"
        ];

        foreach ($patterns as $pattern) {
            Cache::forget($pattern);
        }

        $this->logInfo('Ad cache cleared', ['date' => $date]);
    }

    /**
     * Test ad configuration for deployment
     */
    public function testAdConfiguration(): array
    {
        $results = [
            'passed' => [],
            'failed' => [],
            'warnings' => []
        ];

        // Test script URL
        if ($this->isValidScriptUrl($this->config['script_url'])) {
            $results['passed'][] = 'Script URL format is valid';
        } else {
            $results['failed'][] = 'Script URL format is invalid';
        }

        // Test publisher ID
        if ($this->isValidPublisherId($this->config['publisher_id'])) {
            $results['passed'][] = 'Publisher ID format is valid';
        } else {
            $results['failed'][] = 'Publisher ID format is invalid';
        }

        // Test ad zones
        $zones = $this->filterAdZones();
        if (empty($zones)) {
            $results['warnings'][] = 'No ad zones configured';
        } else {
            $results['passed'][] = count($zones) . ' ad zones configured';
        }

        // Test security settings
        if ($this->config['security']['csp_enabled']) {
            $results['passed'][] = 'CSP security enabled';
        } else {
            $results['warnings'][] = 'CSP security disabled';
        }

        return $results;
    }
} 
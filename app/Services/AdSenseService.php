<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class AdSenseService
{
    protected UserTierService $tierService;
    protected array $config;

    public function __construct(UserTierService $tierService)
    {
        $this->tierService = $tierService;
        $this->config = config('adsense');
    }

    /**
     * Check if ads should be displayed for the current user
     */
    public function shouldShowAds(?User $user = null): bool
    {
        // If AdSense is disabled globally
        if (!$this->config['enabled']) {
            return false;
        }

        // If no user (guest), show ads
        if (!$user) {
            return true;
        }

        // Use existing tier service logic
        return $this->tierService->shouldShowAds($user);
    }

    /**
     * Check if current page should show ads
     */
    public function shouldShowAdsOnPage(Request $request): bool
    {
        $restrictedPages = $this->config['restricted_pages'] ?? [];
        $currentPath = $request->path();

        foreach ($restrictedPages as $restrictedPage) {
            if (str_contains($currentPath, $restrictedPage)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get AdSense configuration for frontend
     */
    public function getAdSenseConfig(?User $user = null): array
    {
        if (!$this->shouldShowAds($user)) {
            return [
                'enabled' => false,
                'reason' => 'paid_user'
            ];
        }

        return [
            'enabled' => true,
            'publisher_id' => $this->config['publisher_id'],
            'auto_ads' => $this->config['auto_ads'],
            'display' => $this->config['display'],
            'privacy' => $this->getPrivacyConfig(),
            'debug' => $this->config['debug']['enabled'],
            'test_mode' => $this->config['test_mode'],
        ];
    }

    /**
     * Get privacy configuration based on user location
     */
    protected function getPrivacyConfig(): array
    {
        return [
            'gdpr_enabled' => $this->config['privacy']['gdpr_enabled'],
            'cookie_consent' => $this->config['privacy']['cookie_consent'],
            'personalized_ads' => $this->config['privacy']['personalized_ads'],
            'data_processing_consent' => $this->config['privacy']['data_processing_consent'],
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
     * Generate AdSense script for auto ads
     */
    public function generateAdSenseScript(): string
    {
        $publisherId = $this->config['publisher_id'];
        $autoAdsConfig = $this->config['auto_ads'];

        if (!$publisherId) {
            Log::warning('AdSense Publisher ID not configured');
            return '';
        }

        $script = "
        <script async src=\"https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={$publisherId}\" crossorigin=\"anonymous\"></script>";

        // Add auto ads configuration if enabled
        if ($autoAdsConfig['enabled']) {
            $script .= "
        <script>
            (adsbygoogle = window.adsbygoogle || []).push({
                google_ad_client: \"{$publisherId}\",
                enable_page_level_ads: " . ($autoAdsConfig['page_level'] ? 'true' : 'false') . ",
                overlays: {bottom: " . ($autoAdsConfig['anchor_ads'] ? 'true' : 'false') . "},
                vignette: {" . ($autoAdsConfig['vignette_ads'] ? '' : 'enable: false') . "}
            });
        </script>";
        }

        return $script;
    }

    /**
     * Log ad impression for analytics
     */
    public function logAdImpression(User $user, string $adType = 'auto', array $metadata = []): void
    {
        if (!$this->config['analytics']['impressions_tracking']) {
            return;
        }

        try {
            // Log to Laravel logs
            Log::info('AdSense Impression', [
                'user_id' => $user->id,
                'user_tier' => $this->tierService->getUserTier($user),
                'ad_type' => $adType,
                'timestamp' => now()->toISOString(),
                'metadata' => $metadata
            ]);

            // Update daily activity tracking
            $this->tierService->recordActivity($user, 'ads_viewed');

            // Cache for reporting
            $cacheKey = "ads_impressions_{$user->id}_" . now()->format('Y-m-d');
            $currentCount = Cache::get($cacheKey, 0);
            Cache::put($cacheKey, $currentCount + 1, now()->endOfDay());

        } catch (\Exception $e) {
            Log::error('Failed to log ad impression', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
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
            'cookie_consent' => $request->cookie('adsense_consent') === 'true',
            'personalized_ads' => $request->cookie('adsense_personalized') === 'true',
            'data_processing' => $request->cookie('adsense_data_processing') === 'true',
        ];
    }

    /**
     * Update user consent preferences
     */
    public function updateConsent(Request $request, array $preferences): void
    {
        $expiry = now()->addYear();

        foreach ($preferences as $key => $value) {
            $cookieName = "adsense_{$key}";
            cookie($cookieName, $value ? 'true' : 'false', $expiry->diffInMinutes());
        }
    }

    /**
     * Get specific ad slot configuration
     */
    public function getAdSlot(string $slotName): ?string
    {
        $slots = $this->config['ad_slots'] ?? [];
        return $slots[$slotName] ?? null;
    }

    /**
     * Check if specific ad placement is enabled
     */
    public function isAdPlacementEnabled(string $placement): bool
    {
        $display = $this->config['display'] ?? [];
        
        switch ($placement) {
            case 'dashboard_feed':
                return $display['dashboard_feed_enabled'] ?? true;
            case 'sidebar':
                return $display['sidebar_ads_enabled'] ?? true;
            default:
                return true;
        }
    }

    /**
     * Generate specific ad unit HTML
     */
    public function generateAdUnit(string $slotName, array $attributes = []): string
    {
        $slot = $this->getAdSlot($slotName);
        $publisherId = $this->config['publisher_id'];
        
        if (!$slot || !$publisherId) {
            return '';
        }

        $defaultAttributes = [
            'class' => 'adsbygoogle',
            'style' => 'display:block',
            'data-ad-client' => $publisherId,
            'data-ad-slot' => $slot,
        ];

        $mergedAttributes = array_merge($defaultAttributes, $attributes);
        
        $attributeString = '';
        foreach ($mergedAttributes as $key => $value) {
            $attributeString .= " {$key}=\"{$value}\"";
        }

        return "<ins{$attributeString}></ins>";
    }

    /**
     * Log specific ad type impression
     */
    public function logSpecificAdImpression(User $user, string $adType, string $placement, array $metadata = []): void
    {
        $this->logAdImpression($user, $adType, array_merge($metadata, [
            'placement' => $placement,
            'slot' => $this->getAdSlot($adType)
        ]));
    }
} 
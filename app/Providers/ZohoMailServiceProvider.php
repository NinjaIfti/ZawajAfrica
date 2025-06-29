<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\ZohoMailService;
use App\Services\ZohoHttpEmailService;
use Illuminate\Support\Facades\Log;

class ZohoMailServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register the ZohoHttpEmailService as a singleton
        $this->app->singleton(ZohoHttpEmailService::class, function ($app) {
            return new ZohoHttpEmailService();
        });

        // Register the ZohoMailService as a singleton
        $this->app->singleton(ZohoMailService::class, function ($app) {
            return new ZohoMailService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Only configure if Zoho Mail is enabled
        if (config('services.zoho_mail.enabled')) {
            $this->configureZohoMail();
        }
    }

    /**
     * Configure Zoho Mail HTTP service
     */
    private function configureZohoMail(): void
    {
        try {
            $zohoMailService = $this->app->make(ZohoMailService::class);
            
            if ($zohoMailService->isConfigured()) {
                Log::debug('ZohoMailServiceProvider: Zoho HTTP API configured successfully');
            } else {
                Log::warning('ZohoMailServiceProvider: Zoho HTTP API enabled but not properly configured');
                Log::info('Make sure to set ZOHO_MAIL_API_TOKEN in your .env file');
            }
        } catch (\Exception $e) {
            Log::error('ZohoMailServiceProvider: Failed to configure Zoho HTTP API', [
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ]);
        }
    }
} 
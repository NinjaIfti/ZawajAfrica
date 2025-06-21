<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\ZohoMailService;
use Illuminate\Support\Facades\Log;

class ZohoMailServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
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
        // Only configure Zoho Mail if it's enabled
        if (config('services.zoho_mail.enabled')) {
            $this->configureZohoMail();
        }
    }

    /**
     * Configure Zoho Mail SMTP settings
     */
    private function configureZohoMail(): void
    {
        try {
            $zohoMailService = $this->app->make(ZohoMailService::class);
            
            if ($zohoMailService->isConfigured()) {
                // Configure mail settings to use Zoho SMTP
                $zohoMailService->configureMailer();
                
                Log::info('ZohoMailServiceProvider: Mail configured to use Zoho SMTP');
            } else {
                Log::warning('ZohoMailServiceProvider: Zoho Mail enabled but not properly configured');
            }
        } catch (\Exception $e) {
            Log::error('ZohoMailServiceProvider: Failed to configure Zoho Mail', [
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ]);
        }
    }
} 
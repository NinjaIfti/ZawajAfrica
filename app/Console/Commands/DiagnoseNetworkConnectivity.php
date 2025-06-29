<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class DiagnoseNetworkConnectivity extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'network:diagnose {--smtp-only : Only test SMTP connectivity}';

    /**
     * The console command description.
     */
    protected $description = 'Diagnose network connectivity issues for email and external services';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Network Connectivity Diagnosis Tool');
        $this->info('=====================================');

        // Test SMTP connectivity
        $this->testSMTPConnectivity();

        if (!$this->option('smtp-only')) {
            // Test general internet connectivity
            $this->testInternetConnectivity();
            
            // Test DNS resolution
            $this->testDNSResolution();
        }

        $this->info("\n📋 Diagnosis Complete");
    }

    /**
     * Test SMTP server connectivity
     */
    private function testSMTPConnectivity()
    {
        $this->info("\n📧 Testing SMTP Connectivity");
        $this->info("=============================");

        $smtpConfigs = [
            'Zoho SMTP' => [
                'host' => config('services.zoho_mail.smtp_host', 'smtp.zoho.com'),
                'port' => config('services.zoho_mail.smtp_port', 587),
            ],
            'Zoho SMTP SSL' => [
                'host' => config('services.zoho_mail.smtp_host', 'smtp.zoho.com'),
                'port' => 465,
            ],
            'Gmail SMTP (Alternative)' => [
                'host' => 'smtp.gmail.com',
                'port' => 587,
            ],
        ];

        foreach ($smtpConfigs as $name => $config) {
            $this->info("Testing {$name} ({$config['host']}:{$config['port']})...");
            
            $result = $this->testPortConnectivity($config['host'], $config['port'], 10);
            
            if ($result['success']) {
                $this->info("✅ {$name}: Connected successfully in {$result['time']}s");
            } else {
                $this->error("❌ {$name}: {$result['error']}");
                
                if (strpos($result['error'], 'timeout') !== false) {
                    $this->warn("   💡 This looks like a firewall or network issue");
                }
            }
        }
    }

    /**
     * Test general internet connectivity
     */
    private function testInternetConnectivity()
    {
        $this->info("\n🌐 Testing Internet Connectivity");
        $this->info("=================================");

        $testUrls = [
            'Google' => 'https://www.google.com',
            'Cloudflare DNS' => 'https://1.1.1.1',
            'OpenAI API' => 'https://api.openai.com',
        ];

        foreach ($testUrls as $name => $url) {
            $this->info("Testing {$name}...");
            
            try {
                $startTime = microtime(true);
                $response = Http::timeout(10)->get($url);
                $endTime = microtime(true);
                $duration = round(($endTime - $startTime), 2);
                
                if ($response->successful()) {
                    $this->info("✅ {$name}: Connected successfully in {$duration}s");
                } else {
                    $this->warn("⚠️ {$name}: HTTP {$response->status()}");
                }
            } catch (\Exception $e) {
                $this->error("❌ {$name}: {$e->getMessage()}");
            }
        }
    }

    /**
     * Test DNS resolution
     */
    private function testDNSResolution()
    {
        $this->info("\n🔍 Testing DNS Resolution");
        $this->info("==========================");

        $hostnames = [
            'smtp.zoho.com',
            'google.com',
            'api.openai.com',
        ];

        foreach ($hostnames as $hostname) {
            $this->info("Resolving {$hostname}...");
            
            try {
                $ip = gethostbyname($hostname);
                
                if ($ip !== $hostname) {
                    $this->info("✅ {$hostname} → {$ip}");
                } else {
                    $this->error("❌ {$hostname}: DNS resolution failed");
                }
            } catch (\Exception $e) {
                $this->error("❌ {$hostname}: {$e->getMessage()}");
            }
        }
    }

    /**
     * Test port connectivity
     */
    private function testPortConnectivity($host, $port, $timeout = 10)
    {
        $startTime = microtime(true);
        
        try {
            $connection = @fsockopen($host, $port, $errno, $errstr, $timeout);
            $endTime = microtime(true);
            $duration = round(($endTime - $startTime), 2);
            
            if ($connection) {
                fclose($connection);
                return [
                    'success' => true,
                    'time' => $duration,
                ];
            } else {
                return [
                    'success' => false,
                    'error' => "Connection failed: {$errstr} (Error {$errno})",
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => "Exception: " . $e->getMessage(),
            ];
        }
    }
} 
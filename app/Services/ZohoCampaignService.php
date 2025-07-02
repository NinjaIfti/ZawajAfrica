<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use Exception;

class ZohoCampaignService
{
    private string $baseUrl;
    private string $clientId;
    private string $clientSecret;
    private string $refreshToken;
    private string $fromEmail;
    private string $fromName;
    private string $dataCenter;

    public function __construct()
    {
        $this->dataCenter = config('services.zoho_campaign.data_center', 'com');
        $this->baseUrl = "https://campaigns.zoho.{$this->dataCenter}/api/v1.1";
        $this->clientId = config('services.zoho_campaign.client_id');
        $this->clientSecret = config('services.zoho_campaign.client_secret');
        $this->refreshToken = config('services.zoho_campaign.refresh_token');
        $this->fromEmail = config('services.zoho_campaign.from_email');
        $this->fromName = config('services.zoho_campaign.from_name');
    }

    /**
     * Check if the service is properly configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->clientId) && 
               !empty($this->clientSecret) && 
               !empty($this->refreshToken) &&
               !empty($this->fromEmail);
    }

    /**
     * Get or refresh access token
     */
    private function getAccessToken(): ?string
    {
        $cacheKey = 'zoho_campaign_access_token';
        $accessToken = Cache::get($cacheKey);

        if ($accessToken) {
            return $accessToken;
        }

        try {
            $tokenUrl = "https://accounts.zoho.com/oauth/v2/token";
            $response = Http::asForm()->timeout(30)->post($tokenUrl, [
                'grant_type' => 'refresh_token',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'refresh_token' => $this->refreshToken,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Check if the response contains an error (Zoho returns 200 even for errors)
                if (isset($data['error'])) {
                    Log::error('Zoho Campaign token refresh failed with error', [
                        'error' => $data['error'],
                        'error_description' => $data['error_description'] ?? '',
                        'response_body' => $response->body(),
                        'request_data' => [
                            'client_id' => $this->clientId,
                            'client_secret_length' => strlen($this->clientSecret ?? ''),
                            'refresh_token_length' => strlen($this->refreshToken ?? ''),
                            'token_url' => $tokenUrl
                        ]
                    ]);
                    return null;
                }
                
                $accessToken = $data['access_token'] ?? null;
                $expiresIn = $data['expires_in'] ?? 3600;

                // Debug logging
                Log::info('Zoho Campaign token refresh successful', [
                    'access_token_exists' => isset($data['access_token']),
                    'expires_in' => $expiresIn
                ]);

                if ($accessToken) {
                    // Cache for slightly less than the actual expiry
                    Cache::put($cacheKey, $accessToken, $expiresIn - 60);
                    return $accessToken;
                } else {
                    Log::error('Access token is null despite successful response', [
                        'response_body' => $response->body(),
                        'parsed_data' => $data
                    ]);
                }
            }

            Log::error('Failed to refresh Zoho Campaign access token', [
                'response' => $response->json(),
                'status' => $response->status(),
                'body' => $response->body(),
                'request_data' => [
                    'client_id' => $this->clientId,
                    'client_secret_length' => strlen($this->clientSecret ?? ''),
                    'refresh_token_length' => strlen($this->refreshToken ?? ''),
                    'token_url' => $tokenUrl
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Exception while refreshing Zoho Campaign access token', [
                'error' => $e->getMessage()
            ]);
        }

        return null;
    }

    /**
     * Make authenticated API request
     */
    private function makeRequest(string $method, string $endpoint, array $data = []): array
    {
        $accessToken = $this->getAccessToken();
        
        if (!$accessToken) {
            return [
                'success' => false,
                'error' => 'Unable to obtain access token'
            ];
        }

        try {
            $headers = [
                'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
            ];

            // All requests should be GET with query parameters for Zoho Campaign API
            $data['resfmt'] = 'JSON'; // Always request JSON format
            
            $response = Http::withHeaders($headers)->timeout(30)->get($this->baseUrl . $endpoint, $data);

            Log::info('Zoho Campaign API request details', [
                'method' => $method,
                'endpoint' => $endpoint,
                'request_data' => $data,
                'response_status' => $response->status(),
                'response_body' => $response->body(),
                'response_headers' => $response->headers()
            ]);

            if ($response->successful()) {
                $responseBody = $response->body();
                
                // Check if it's XML response (which might contain errors)
                if (str_contains($responseBody, '<?xml')) {
                    // Parse XML to check for errors
                    $xml = simplexml_load_string($responseBody);
                    if ($xml && isset($xml->status) && (string)$xml->status === 'error') {
                        $errorMessage = isset($xml->message) ? (string)$xml->message : 'Unknown API error';
                        $errorCode = isset($xml->code) ? (string)$xml->code : 'unknown';
                        
                        return [
                            'success' => false,
                            'error' => "Zoho API Error ({$errorCode}): {$errorMessage}",
                            'raw_response' => $responseBody
                        ];
                    }
                    
                    // Convert XML to array for processing
                    $data = json_decode(json_encode($xml), true);
                } else {
                    // Try to parse as JSON
                    $data = $response->json();
                }

                return [
                    'success' => true,
                    'data' => $data
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'API request failed: ' . $response->status() . ' - ' . $response->body()
                ];
            }
        } catch (Exception $e) {
            Log::error('Zoho Campaign API request failed', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get all mailing lists
     */
    public function getMailingLists(): array
    {
        $result = $this->makeRequest('GET', '/getmailinglists', []);
        
        if ($result['success']) {
            return [
                'success' => true,
                'lists' => $result['data']['list_of_details'] ?? []
            ];
        }

        return $result;
    }

    /**
     * Create a new mailing list
     */
    public function createMailingList(string $listName, string $description = ''): array
    {
        // Clean the list name to avoid pattern match errors
        $cleanListName = preg_replace('/[^a-zA-Z0-9\s_-]/', '', $listName);
        $cleanListName = trim($cleanListName);
        
        if (empty($cleanListName)) {
            $cleanListName = 'ZawajAfrica_List_' . time();
        }
        
        // Use a valid dummy email for list creation
        $dummyEmail = 'admin@zawajafrica.com.ng';
        
        $data = [
            'resfmt' => 'JSON',
            'listname' => $cleanListName,
            'listdescription' => $description ?: "Mailing list for {$cleanListName}",
            'signupform' => 'private',
            'mode' => 'newlist',
            'emailids' => $dummyEmail
        ];

        Log::info('Creating Zoho Campaign list', [
            'original_name' => $listName,
            'clean_name' => $cleanListName,
            'description' => $data['listdescription']
        ]);

        return $this->makeRequest('GET', '/addlistandcontacts', $data);
    }

    /**
     * Add subscribers to a mailing list (bulk import)
     */
    public function addContactsToList(string $listKey, array $contacts): array
    {
        try {
            // Extract email addresses from contact data
            $emailIds = [];
            foreach ($contacts as $contact) {
                if (isset($contact['Contact Email'])) {
                    $emailIds[] = $contact['Contact Email'];
                }
            }
            
            if (empty($emailIds)) {
                return [
                    'success' => false,
                    'error' => 'No valid email addresses found in contacts'
                ];
            }

            // Zoho Campaign API allows max 10 emails per request for bulk import
            $limitedEmails = array_slice($emailIds, 0, 10);
            $emailString = implode(',', $limitedEmails);
            
            $data = [
                'resfmt' => 'JSON',
                'listkey' => $listKey,
                'emailids' => $emailString
            ];

            Log::info('Adding subscribers to Zoho Campaign list', [
                'list_key' => $listKey,
                'contact_count' => count($contacts),
                'email_count' => count($limitedEmails),
                'email_sample' => array_slice($limitedEmails, 0, 3)
            ]);

            // Use the bulk subscribers API endpoint
            $result = $this->makeRequest('GET', '/addlistsubscribersinbulk', $data);
            
            if ($result['success']) {
                Log::info('Successfully added subscribers to Zoho Campaign list', [
                    'list_key' => $listKey,
                    'contact_count' => count($contacts)
                ]);
            }
            
            return $result;
            
        } catch (Exception $e) {
            Log::error('Error adding subscribers to Zoho Campaign list', [
                'list_key' => $listKey,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Import users to Zoho Campaign
     */
    public function importUsers(string $audience = 'all', ?string $listName = '', ?string $existingListKey = ''): array
    {
        try {
            // Get users based on audience
            $users = $this->getUsersByAudience($audience);

            if ($users->isEmpty()) {
                return [
                    'success' => false,
                    'error' => 'No users found for the selected audience'
                ];
            }

            $listKey = null;
            $listDisplayName = '';

            // Use existing list if provided
            if (!empty($existingListKey)) {
                $listKey = $existingListKey;
                $listDisplayName = 'existing list (' . substr($existingListKey, 0, 8) . '...)';
                
                Log::info('Using existing Zoho Campaign list', [
                    'list_key' => $existingListKey,
                    'audience' => $audience
                ]);
            } else {
                // Create new mailing list
                if (empty($listName)) {
                    $listName = ucfirst($audience) . ' Users - ' . now()->format('M d, Y');
                }

                $listResult = $this->createMailingList($listName);
                
                if (!$listResult['success']) {
                    return [
                        'success' => false,
                        'error' => 'Failed to create mailing list: ' . $listResult['error']
                    ];
                }

                // Debug logging to see the actual response structure
                Log::info('Zoho Campaign list creation response', [
                    'response_data' => $listResult['data'],
                    'full_response' => $listResult
                ]);

                // Extract list key from successful response
                $listKey = $listResult['data']['listkey'] ?? null;
                
                if (!$listKey) {
                    Log::error('Failed to extract list key from response', [
                        'response_structure' => $listResult['data'],
                        'available_keys' => array_keys($listResult['data'] ?? [])
                    ]);
                    
                    return [
                        'success' => false,
                        'error' => 'Failed to get list key from created list. Response: ' . json_encode($listResult['data'])
                    ];
                }
                
                $listDisplayName = "'{$listName}'";
            }

            // Prepare contacts for import
            $contacts = [];
            foreach ($users as $user) {
                $contact = [
                    'Contact Email' => $user->email,
                    'First Name' => $user->name,
                    'Last Name' => '', // You might want to split the name
                    'User Type' => $user->subscription_type ?: 'free',
                    'Registration Date' => $user->created_at->format('Y-m-d'),
                ];

                // Add additional fields if available
                if ($user->profile) {
                    $contact['Phone'] = $user->profile->phone ?? '';
                }

                $contacts[] = $contact;
            }

            // Add contacts to list in batches (Zoho Campaign API limit: 10 emails per request)
            $batchSize = 10;
            $batches = array_chunk($contacts, $batchSize);
            $totalAdded = 0;
            $totalErrors = 0;

            foreach ($batches as $batch) {
                $addResult = $this->addContactsToList($listKey, $batch);
                
                if ($addResult['success']) {
                    $totalAdded += count($batch);
                    Log::info('Added batch to Zoho Campaign', [
                        'list_key' => $listKey,
                        'batch_size' => count($batch),
                        'total_added' => $totalAdded
                    ]);
                } else {
                    $totalErrors++;
                    Log::error('Failed to add batch to Zoho Campaign', [
                        'list_key' => $listKey,
                        'batch_size' => count($batch),
                        'error' => $addResult['error']
                    ]);
                }
            }

            $message = "Successfully imported {$totalAdded} users to {$listDisplayName}";
            if ($totalErrors > 0) {
                $message .= " ({$totalErrors} batches failed)";
            }

            return [
                'success' => true,
                'message' => $message,
                'list_key' => $listKey,
                'users_imported' => $totalAdded,
                'failed_batches' => $totalErrors
            ];

        } catch (Exception $e) {
            Log::error('Failed to import users to Zoho Campaign', [
                'error' => $e->getMessage(),
                'audience' => $audience
            ]);

            return [
                'success' => false,
                'error' => 'Import failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create and send a campaign
     */
    public function createCampaign(string $listKey, string $subject, string $content, string $campaignName = ''): array
    {
        try {
            // Generate campaign name if not provided
            if (empty($campaignName)) {
                $campaignName = $subject . ' - ' . now()->format('M d, Y');
            }

            // Create campaign
            $campaignData = [
                'campaignname' => $campaignName,
                'subject' => $subject,
                'fromemailid' => $this->fromEmail,
                'fromemail' => $this->fromName,
                'list' => [$listKey],
                'htmlcontent' => $this->formatHtmlContent($content),
                'textcontent' => strip_tags($content)
            ];

            $createResult = $this->makeRequest('POST', '/campaigns', $campaignData);

            if (!$createResult['success']) {
                return [
                    'success' => false,
                    'error' => 'Failed to create campaign: ' . $createResult['error']
                ];
            }

            $campaignKey = $createResult['data']['campaign_key'] ?? null;

            if (!$campaignKey) {
                return [
                    'success' => false,
                    'error' => 'Failed to get campaign key from created campaign'
                ];
            }

            // Schedule campaign to send immediately
            $scheduleResult = $this->makeRequest('POST', "/campaigns/{$campaignKey}/actions/send");

            if ($scheduleResult['success']) {
                return [
                    'success' => true,
                    'message' => "Campaign '{$campaignName}' created and scheduled successfully",
                    'campaign_key' => $campaignKey
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Campaign created but failed to schedule: ' . $scheduleResult['error']
                ];
            }

        } catch (Exception $e) {
            Log::error('Failed to create Zoho Campaign', [
                'error' => $e->getMessage(),
                'list_key' => $listKey,
                'subject' => $subject
            ]);

            return [
                'success' => false,
                'error' => 'Campaign creation failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get campaign statistics
     */
    public function getCampaignStats(string $campaignKey): array
    {
        return $this->makeRequest('GET', "/campaigns/{$campaignKey}/stats");
    }

    /**
     * Test the connection to Zoho Campaign API
     */
    public function testConnection(): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'Zoho Campaign is not properly configured'
            ];
        }

        $result = $this->getMailingLists();
        
        if ($result['success']) {
            return [
                'success' => true,
                'message' => 'Successfully connected to Zoho Campaign API'
            ];
        }

        return [
            'success' => false,
            'error' => 'Failed to connect to Zoho Campaign API: ' . $result['error']
        ];
    }

    /**
     * Get users based on audience type
     */
    private function getUsersByAudience(string $audience): \Illuminate\Database\Eloquent\Collection
    {
        $query = User::whereNotNull('email')->where('email', '!=', '');

        switch ($audience) {
            case 'premium':
                $query->where('subscription_type', 'premium')
                      ->where('subscription_end_date', '>', now());
                break;
            case 'basic':
                $query->where('subscription_type', 'basic')
                      ->where('subscription_end_date', '>', now());
                break;
            case 'free':
                $query->where(function($q) {
                    $q->whereNull('subscription_type')
                      ->orWhere('subscription_type', 'free')
                      ->orWhere('subscription_end_date', '<=', now());
                });
                break;
            case 'all':
            default:
                // No additional filtering for 'all'
                break;
        }

        return $query->with('profile')->get();
    }

    /**
     * Format content as HTML for email
     */
    private function formatHtmlContent(string $content): string
    {
        // Convert line breaks to <br> tags
        $htmlContent = nl2br($content);
        
        // Wrap in basic HTML structure
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1'>
            <title>{$this->fromName}</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='text-align: center; margin-bottom: 30px;'>
                <h1 style='color: #6B4FA0;'>{$this->fromName}</h1>
            </div>
            <div style='background: #f9f9f9; padding: 20px; border-radius: 8px;'>
                {$htmlContent}
            </div>
            <div style='text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; font-size: 14px;'>
                <p>Best regards,<br>The {$this->fromName} Team</p>
                <p><a href='https://zawajafrica.com.ng' style='color: #6B4FA0;'>Visit ZawajAfrica</a></p>
            </div>
        </body>
        </html>";
    }

    /**
     * Get service status
     */
    public function getStatus(): array
    {
        return [
            'configured' => $this->isConfigured(),
            'client_id' => $this->clientId,
            'has_client_secret' => !empty($this->clientSecret),
            'has_refresh_token' => !empty($this->refreshToken),
            'from_email' => $this->fromEmail,
            'from_name' => $this->fromName,
            'data_center' => $this->dataCenter,
            'base_url' => $this->baseUrl
        ];
    }
}

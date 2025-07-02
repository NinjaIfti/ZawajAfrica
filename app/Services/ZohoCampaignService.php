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

                Log::info('Zoho Campaign token refresh successful', [
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
     * @param string $method
     * @param string $endpoint
     * @param array $data
     * @param bool $isFullUrl If true, $endpoint is a full URL and should not be prefixed with baseUrl
     */
    private function makeRequest(string $method, string $endpoint, array $data = [], bool $isFullUrl = false): array
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

            $data['resfmt'] = 'JSON'; // Always request JSON format
            
            $url = $isFullUrl ? $endpoint : $this->baseUrl . $endpoint;

            if (strtoupper($method) === 'POST') {
                $response = Http::withHeaders($headers)
                    ->asForm()
                    ->timeout(30)
                    ->post($url, $data);
            } else {
                $response = Http::withHeaders($headers)
                    ->timeout(30)
                    ->get($url, $data);
            }

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
                    
                    // Check for JSON error responses
                    if (isset($data['status']) && $data['status'] === 'error') {
                        $errorMessage = $data['message'] ?? 'Unknown API error';
                        $errorCode = $data['Code'] ?? $data['code'] ?? 'unknown';
                        
                        return [
                            'success' => false,
                            'error' => "Zoho API Error ({$errorCode}): {$errorMessage}",
                            'raw_response' => $responseBody
                        ];
                    }
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
                'error' => 'Exception: ' . $e->getMessage()
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

                Log::info('Zoho Campaign list creation response', [
                    'list_key' => $listResult['data']['listkey'] ?? 'unknown'
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
     * Create a topic for campaign creation (required for accounts with topic management)
     */
    public function createTopic(string $topicName = 'Default Campaign Topic', string $topicDesc = 'Default topic for email campaigns'): array
    {
        try {
            // Use v1.1 endpoint with correct parameter names as form data
            $topicData = [
                'topic_name' => $topicName,
                'topic_desc' => $topicDesc
            ];
            
            // Add product_id if configured (might be required for some accounts)
            $productId = config('services.zoho_campaign.product_id');
            if (!empty($productId)) {
                $topicData['product_id'] = $productId;
                Log::info('Adding product_id to topic creation', ['product_id' => $productId]);
            }
            
            Log::info('Creating Zoho topic with data', [
                'topic_data' => $topicData
            ]);
            
            // Try v1.1 first, then fallback to v2 if it redirects
            $result = $this->makeRequest('POST', '/topics', $topicData);
            
            // If v1.1 fails and response indicates v2 redirect, try v2 directly
            if (!$result['success'] && isset($result['data']['uri']) && str_contains($result['data']['uri'], '/api/v2/')) {
                Log::info('v1.1 redirected to v2, trying v2 endpoint directly');
                
                // Switch to v2 endpoint temporarily
                $originalBaseUrl = $this->baseUrl;
                $this->baseUrl = "https://campaigns.zoho.{$this->dataCenter}/api/v2";
                
                $result = $this->makeRequest('POST', '/topics', $topicData);
                
                // Restore original base URL
                $this->baseUrl = $originalBaseUrl;
            }
            
            if ($result['success']) {
                Log::info('Zoho topic created successfully', [
                    'topic_data' => $result['data']
                ]);
                
                return $result;
            }
            
            return [
                'success' => false,
                'error' => 'Failed to create topic: ' . ($result['error'] ?? 'Unknown error'),
                'data' => []
            ];
            
        } catch (Exception $e) {
            Log::error('Failed to create Zoho topic', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => 'Topic creation failed: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Get available topics for campaign creation
     */
    public function getTopics(): array
    {
        try {
            Log::info('Fetching topics from Zoho Campaigns');
            
            $result = $this->makeRequest('GET', '/topics');
            
            if ($result['success']) {
                Log::info('Topics fetched successfully', [
                    'topics_data' => $result['data']
                ]);
                
                return $result;
            }
            
            return [
                'success' => false,
                'error' => 'Failed to fetch topics: ' . ($result['error'] ?? 'Unknown error'),
                'data' => []
            ];
            
        } catch (Exception $e) {
            Log::error('Failed to fetch Zoho topics', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => 'Topics fetch failed: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Create a campaign
     */
    public function createCampaign(string $listKey, string $subject, string $content, ?string $campaignName = ''): array
    {
        try {
            // Generate campaign name if not provided
            if (empty($campaignName)) {
                $campaignName = $subject . ' - ' . now()->format('M d, Y');
            }

            // Instead of using a hardcoded template URL, we'll format the content properly
            $formattedContent = $this->formatHtmlContent($content);

            // Try to get existing topics first, then create if none exist
            $topicsResult = $this->getTopics();
            $validTopicId = null;
            
            if ($topicsResult['success'] && !empty($topicsResult['data'])) {
                // Extract topic ID from existing topics
                $topicsData = $topicsResult['data'];
                
                // Check for topicDetails array (as returned by the API)
                if (isset($topicsData['topicDetails']) && is_array($topicsData['topicDetails']) && !empty($topicsData['topicDetails'])) {
                    $firstTopic = $topicsData['topicDetails'][0];
                    $validTopicId = $firstTopic['topicId'] ?? $firstTopic['topic_id'] ?? $firstTopic['id'] ?? null;
                } elseif (isset($topicsData['topics']) && is_array($topicsData['topics']) && !empty($topicsData['topics'])) {
                    $firstTopic = $topicsData['topics'][0];
                    $validTopicId = $firstTopic['topic_id'] ?? $firstTopic['topicId'] ?? $firstTopic['id'] ?? null;
                } elseif (is_array($topicsData) && !empty($topicsData)) {
                    // If topics data is directly an array
                    $firstTopic = $topicsData[0];
                    $validTopicId = $firstTopic['topic_id'] ?? $firstTopic['topicId'] ?? $firstTopic['id'] ?? null;
                }
                
                Log::info('Found existing topics', [
                    'topics_data' => $topicsData,
                    'extracted_topic_id' => $validTopicId
                ]);
            } else {
                // If no topics exist, try to create one
                Log::info('No existing topics found, attempting to create one');
                $topicResult = $this->createTopic("ZawajAfrica Campaign Topic", "Topic for ZawajAfrica email campaigns");
                
                if ($topicResult['success'] && !empty($topicResult['data'])) {
                    $topicData = $topicResult['data'];
                    $validTopicId = $topicData['topic_id'] ?? $topicData['topicId'] ?? $topicData['id'] ?? null;
                    
                    Log::info('Topic creation result', [
                        'topic_data' => $topicData,
                        'extracted_topic_id' => $validTopicId
                    ]);
                }
            }

            // Use the v1.1 endpoint consistently
            $fullUrl = "https://campaigns.zoho.{$this->dataCenter}/api/v1.1/createCampaign";
            $campaignData = [
                'resfmt' => 'JSON',
                'campaignname' => $campaignName,
                'from_email' => $this->fromEmail,
                'fromname' => $this->fromName,
                'subject' => $subject,
                'htmlcontent' => $formattedContent, // Use htmlcontent instead of content_url
                'list_details' => json_encode([$listKey => []])
            ];
            
            // Add topicId (required for accounts with topic management)
            $configTopicId = config('services.zoho_campaign.topic_id');
            if (!empty($configTopicId)) {
                $campaignData['topicId'] = $configTopicId;
            } elseif (!empty($validTopicId)) {
                $campaignData['topicId'] = $validTopicId;
                Log::info('Using created topicId', ['topicId' => $validTopicId]);
            } else {
                // Try without topicId first - maybe it's not actually required
                Log::info('No topicId available, trying without it');
            }
            
            Log::info('Zoho createCampaign request data', ['request_data' => $campaignData]);
            $createResult = $this->makeRequest('POST', $fullUrl, $campaignData, true);
            Log::info('Raw Zoho createCampaign response', [ 'result' => $createResult ]);
            
            // If v1.1 fails and response indicates v2 redirect, try v2 directly
            if (!$createResult['success'] && isset($createResult['data']['uri']) && str_contains($createResult['data']['uri'], '/api/v2/')) {
                Log::info('Campaign creation v1.1 redirected to v2, trying v2 endpoint directly');
                
                $v2Url = "https://campaigns.zoho.{$this->dataCenter}/api/v2/createCampaign";
                $createResult = $this->makeRequest('POST', $v2Url, $campaignData, true);
                Log::info('v2 createCampaign response', [ 'result' => $createResult ]);
            }

            if (!$createResult['success']) {
                Log::error('Campaign creation failed', [
                    'error' => $createResult['error'],
                    'response_data' => $createResult['data'] ?? null
                ]);
                return [
                    'success' => false,
                    'error' => 'Failed to create campaign: ' . $createResult['error'],
                    'raw_response' => $createResult
                ];
            }
            
            Log::info('Campaign creation successful', [
                'campaign_key' => $createResult['data']['campaignKey'] ?? $createResult['data']['campaign_key'] ?? 'unknown'
            ]);

            // Check for campaignKey in different possible field names
            $campaignKey = $createResult['data']['campaignKey'] ?? 
                          $createResult['data']['campaign_key'] ?? 
                          $createResult['data']['CampaignKey'] ?? null;

            if (!$campaignKey) {
                return [
                    'success' => false,
                    'error' => 'Failed to get campaign key from created campaign'
                ];
            }

            return [
                'success' => true,
                'message' => "Campaign '{$campaignName}' created successfully",
                'campaign_key' => $campaignKey
            ];

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
     * Send a campaign that has been created
     * Uses the correct Zoho Campaign API v1.1 endpoint
     */
    public function sendCampaign(string $campaignKey): array
    {
        try {
            // Correct endpoint from Zoho documentation
            $sendUrl = "https://campaigns.zoho.{$this->dataCenter}/api/v1.1/sendcampaign";
            $sendData = [
                'resfmt' => 'JSON',
                'campaignkey' => $campaignKey  // Lowercase 'campaignkey' per documentation
            ];

            Log::info('Sending Zoho Campaign', [
                'campaign_key' => $campaignKey,
                'request_data' => $sendData
            ]);

            $result = $this->makeRequest('POST', $sendUrl, $sendData, true);
            
            if (!$result['success']) {
                Log::error('Campaign sending failed', [
                    'campaign_key' => $campaignKey,
                    'error' => $result['error'],
                    'response_data' => $result['data'] ?? null
                ]);
                return [
                    'success' => false,
                    'error' => 'Failed to send campaign: ' . $result['error']
                ];
            }

            Log::info('Campaign sent successfully', [
                'campaign_key' => $campaignKey,
                'response_data' => $result['data']
            ]);

            return [
                'success' => true,
                'message' => 'Campaign sent successfully',
                'data' => $result['data']
            ];

        } catch (Exception $e) {
            Log::error('Failed to send Zoho Campaign', [
                'error' => $e->getMessage(),
                'campaign_key' => $campaignKey
            ]);

            return [
                'success' => false,
                'error' => 'Campaign sending failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create and immediately send a campaign
     * Now with working send functionality
     */
    public function createAndSendCampaign(string $listKey, string $subject, string $content, ?string $campaignName = '', bool $sendImmediately = false): array
    {
        // First create the campaign
        $createResult = $this->createCampaign($listKey, $subject, $content, $campaignName);
        
        if (!$createResult['success']) {
            return $createResult;
        }

        // If sendImmediately is true, send the campaign
        if ($sendImmediately) {
            $campaignKey = $createResult['campaign_key'];
            $sendResult = $this->sendCampaign($campaignKey);
            
            if (!$sendResult['success']) {
                return [
                    'success' => false,
                    'error' => 'Campaign created but failed to send: ' . $sendResult['error'],
                    'campaign_key' => $campaignKey
                ];
            }

            return [
                'success' => true,
                'message' => 'Campaign created and sent successfully',
                'campaign_key' => $campaignKey,
                'send_data' => $sendResult['data']
            ];
        }

        return $createResult;
    }

    /**
     * Get campaign statistics
     */
    public function getCampaignStats(string $campaignKey): array
    {
        return $this->makeRequest('GET', "/campaigns/{$campaignKey}/stats");
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
        // For Zoho Campaign API, we need to keep the content short
        // Convert line breaks to <br> tags and add basic styling
        $htmlContent = nl2br(htmlspecialchars($content));
        
        // Create a simple HTML email without full document structure
        return "<div style='font-family: Arial, sans-serif; color: #333; padding: 10px;'>" .
               "<h2 style='color: #6B4FA0; text-align: center;'>{$this->fromName}</h2>" .
               "<div style='background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 10px 0;'>" .
               $htmlContent .
               "</div>" .
               "<p style='text-align: center; font-size: 12px; color: #666;'>Best regards,<br>The {$this->fromName} Team</p>" .
               "</div>";
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

    /**
     * Get recently sent campaigns
     */
    public function getRecentCampaigns(): array
    {
        return $this->makeRequest('GET', '/recentsentcampaigns', []);
    }
}

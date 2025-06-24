<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ZohoBookingsService
{
    private $clientId;
    private $clientSecret;
    private $refreshToken;
    private $accessToken;
    private $apiBaseUrl;
    private $organizationId;
    private $dataCenter;

    public function __construct()
    {
        $this->clientId = config('services.zoho_bookings.client_id');
        $this->clientSecret = config('services.zoho_bookings.client_secret');
        $this->refreshToken = config('services.zoho_bookings.refresh_token');
        $this->organizationId = config('services.zoho_bookings.organization_id');
        $this->dataCenter = config('services.zoho_bookings.data_center', 'com');
        $this->apiBaseUrl = "https://www.zohoapis.{$this->dataCenter}/bookings/v1/json";
    }

    /**
     * Check if Zoho Bookings is properly configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->clientId) && 
               !empty($this->clientSecret) && 
               !empty($this->refreshToken) && 
               !empty($this->organizationId);
    }

    /**
     * Get or refresh access token
     */
    private function getAccessToken(): ?string
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        // Try to get cached token
        $cachedToken = Cache::get('zoho_bookings_access_token');
        if ($cachedToken) {
            $this->accessToken = $cachedToken;
            return $this->accessToken;
        }

        // Refresh token if not cached or expired
        return $this->refreshAccessToken();
    }

    /**
     * Refresh access token using refresh token
     */
    private function refreshAccessToken(): ?string
    {
        try {
            $response = Http::asForm()->post("https://accounts.zoho.{$this->dataCenter}/oauth/v2/token", [
                'refresh_token' => $this->refreshToken,
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type' => 'refresh_token'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->accessToken = $data['access_token'];
                
                // Cache token for 55 minutes (tokens expire in 1 hour)
                Cache::put('zoho_bookings_access_token', $this->accessToken, 3300);
                
                Log::info('Zoho Bookings access token refreshed successfully');
                return $this->accessToken;
            }

            Log::error('Failed to refresh Zoho Bookings access token', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);

        } catch (\Exception $e) {
            Log::error('Exception refreshing Zoho Bookings access token', [
                'error' => $e->getMessage()
            ]);
        }

        return null;
    }

    /**
     * Make authenticated API request to Zoho Bookings
     */
    private function makeApiRequest(string $method, string $endpoint, array $data = [], int $retryCount = 0): array
    {
        $maxRetries = 3;
        $retryDelay = 1; // seconds
        
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return ['success' => false, 'error' => 'Unable to get access token', 'retry_needed' => false];
        }

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
                ]);

            $url = $this->apiBaseUrl . $endpoint;

            switch (strtoupper($method)) {
                case 'GET':
                    $httpResponse = $response->get($url, $data);
                    break;
                case 'POST':
                    // Zoho Bookings API expects form data for POST requests
                    $httpResponse = $response->asForm()->post($url, $data);
                    break;
                case 'PUT':
                    $httpResponse = $response->asForm()->put($url, $data);
                    break;
                case 'DELETE':
                    $httpResponse = $response->delete($url, $data);
                    break;
                default:
                    return ['success' => false, 'error' => 'Invalid HTTP method', 'retry_needed' => false];
            }

            if ($httpResponse->successful()) {
                return [
                    'success' => true,
                    'data' => $httpResponse->json()
                ];
            }

            // Handle specific HTTP status codes
            $statusCode = $httpResponse->status();
            $shouldRetry = in_array($statusCode, [429, 500, 502, 503, 504]) && $retryCount < $maxRetries;
            
            if ($statusCode === 401 && $retryCount < 1) {
                // Token might be expired, try to refresh and retry once
                Cache::forget('zoho_bookings_access_token');
                $this->accessToken = null;
                sleep($retryDelay);
                return $this->makeApiRequest($method, $endpoint, $data, $retryCount + 1);
            }

            if ($shouldRetry) {
                sleep($retryDelay * ($retryCount + 1)); // Exponential backoff
                return $this->makeApiRequest($method, $endpoint, $data, $retryCount + 1);
            }

            Log::error('Zoho Bookings API request failed', [
                'method' => $method,
                'endpoint' => $endpoint,
                'status' => $statusCode,
                'response' => $httpResponse->body(),
                'retry_count' => $retryCount
            ]);

            return [
                'success' => false,
                'error' => 'API request failed: HTTP ' . $statusCode,
                'status' => $statusCode,
                'response' => $httpResponse->json(),
                'retry_needed' => false
            ];

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Network/connection errors - these are retryable
            if ($retryCount < $maxRetries) {
                sleep($retryDelay * ($retryCount + 1));
                return $this->makeApiRequest($method, $endpoint, $data, $retryCount + 1);
            }
            
            Log::error('Zoho Bookings connection error', [
                'method' => $method,
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
                'retry_count' => $retryCount
            ]);

            return [
                'success' => false,
                'error' => 'Connection error: ' . $e->getMessage(),
                'retry_needed' => false
            ];

        } catch (\Exception $e) {
            Log::error('Exception in Zoho Bookings API request', [
                'method' => $method,
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => 'Unexpected error: ' . $e->getMessage(),
                'retry_needed' => false
            ];
        }
    }

    /**
     * Get all services (therapists) from Zoho Bookings
     */
    public function getServices(): array
    {
        return $this->makeApiRequest('GET', '/services');
    }

    /**
     * Get available time slots for a service
     */
    public function getAvailableSlots(string $serviceId, string $date): array
    {
        $endpoint = "/freeslots";
        $params = [
            'service_id' => $serviceId,
            'selected_date' => $date,
            'timezone' => config('app.timezone', 'UTC')
        ];

        return $this->makeApiRequest('GET', $endpoint, $params);
    }

    /**
     * Create a new booking in Zoho Bookings
     */
    public function createBooking(array $bookingData): array
    {
        $endpoint = '/appointment';
        
        // Format the booking data according to Zoho Bookings API requirements
        $formData = [
            'service_id' => $bookingData['service_id'],
            'staff_id' => $bookingData['staff_id'], // Required - we need to handle this
            'from_time' => $bookingData['from_time'], // Format: dd-MMM-yyyy HH:mm:ss
            'timezone' => $bookingData['timezone'] ?? config('app.timezone', 'UTC'),
            'customer_details' => $bookingData['customer_details'], // Already JSON formatted
            'notes' => $bookingData['notes'] ?? ''
        ];

        return $this->makeApiRequest('POST', $endpoint, $formData);
    }

    /**
     * Create or sync therapist as staff in Zoho Bookings
     */
    public function createStaff(array $staffData): array
    {
        $endpoint = '/addstaff';
        
        // Format staff data according to Zoho Bookings API
        $formData = [
            'staffMap' => json_encode([
                'data' => [
                    [
                        'name' => $staffData['name'],
                        'email' => $staffData['email'],
                        'role' => 'Staff', // Can be Admin, Manager, or Staff
                        'phone' => $staffData['phone'] ?? '',
                        'designation' => $staffData['designation'] ?? 'Therapist',
                        'assigned_services' => $staffData['assigned_services'] ?? []
                    ]
                ]
            ])
        ];

        return $this->makeApiRequest('POST', $endpoint, $formData);
    }

    /**
     * Update a booking in Zoho Bookings
     */
    public function updateBooking(string $bookingId, array $updateData): array
    {
        $endpoint = "/updateappointment";
        $data = array_merge(['booking_id' => $bookingId], $updateData);
        return $this->makeApiRequest('POST', $endpoint, $data);
    }

    /**
     * Cancel a booking in Zoho Bookings
     */
    public function cancelBooking(string $bookingId, string $reason = ''): array
    {
        $endpoint = "/cancelappointment";
        $data = [
            'booking_id' => $bookingId,
            'reason' => $reason
        ];

        return $this->makeApiRequest('POST', $endpoint, $data);
    }

    /**
     * Get booking details from Zoho Bookings
     */
    public function getBooking(string $bookingId): array
    {
        $endpoint = "/getappointment";
        return $this->makeApiRequest('GET', $endpoint, ['booking_id' => $bookingId]);
    }

    /**
     * Get all bookings with filters
     */
    public function getBookings(array $filters = []): array
    {
        $endpoint = '/getappointments';
        return $this->makeApiRequest('GET', $endpoint, $filters);
    }

    /**
     * Sync therapist data with Zoho Bookings services
     */
    public function syncTherapistWithZoho(array $therapistData): array
    {
        $serviceData = [
            'service_name' => $therapistData['name'],
            'service_description' => $therapistData['bio'],
            'duration' => 60, // 1 hour sessions
            'price' => $therapistData['hourly_rate'],
            'currency' => 'NGN', // Nigerian Naira
            'category' => 'Therapy Sessions'
        ];

        // For now, create new service (Zoho API for creating services may be different)
        return $this->makeApiRequest('POST', '/service', $serviceData);
    }

    /**
     * Convert Zoho booking to local booking format
     */
    public function convertZohoBookingToLocal(array $zohoBooking): array
    {
        return [
            'zoho_booking_id' => $zohoBooking['booking_id'],
            'appointment_datetime' => Carbon::parse($zohoBooking['from_time']),
            'customer_name' => $zohoBooking['customer_details']['name'],
            'customer_email' => $zohoBooking['customer_details']['email'],
            'customer_phone' => $zohoBooking['customer_details']['phone'] ?? null,
            'status' => $this->mapZohoStatusToLocal($zohoBooking['status']),
            'notes' => $zohoBooking['additional_info'] ?? '',
            'meeting_link' => $zohoBooking['meeting_link'] ?? null,
            'service_id' => $zohoBooking['service_id'],
            'staff_id' => $zohoBooking['staff_id'] ?? null
        ];
    }

    /**
     * Map Zoho booking status to local status
     */
    private function mapZohoStatusToLocal(string $zohoStatus): string
    {
        return match($zohoStatus) {
            'confirmed' => 'confirmed',
            'cancelled' => 'cancelled',
            'completed' => 'completed',
            'no_show' => 'cancelled',
            default => 'pending'
        };
    }

    /**
     * Test Zoho Bookings connection
     */
    public function testConnection(): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Zoho Bookings not configured'
            ];
        }

        $result = $this->makeApiRequest('GET', '/services');
        
        if ($result['success']) {
            return [
                'success' => true,
                'message' => 'Successfully connected to Zoho Bookings',
                'services_count' => count($result['data']['services'] ?? [])
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to connect to Zoho Bookings: ' . ($result['error'] ?? 'Unknown error')
        ];
    }

    /**
     * Get configuration status
     */
    public function getStatus(): array
    {
        return [
            'configured' => $this->isConfigured(),
            'client_id' => $this->clientId ? '***' . substr($this->clientId, -4) : null,
            'organization_id' => $this->organizationId,
            'data_center' => $this->dataCenter,
            'api_base_url' => $this->apiBaseUrl,
            'has_access_token' => !empty($this->getAccessToken())
        ];
    }
} 
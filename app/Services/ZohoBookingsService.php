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
        $this->apiBaseUrl = "https://bookings.zoho.{$this->dataCenter}/api/v1";
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
    private function makeApiRequest(string $method, string $endpoint, array $data = []): array
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return ['success' => false, 'error' => 'Unable to get access token'];
        }

        try {
            $response = Http::withToken($accessToken)
                ->withHeaders([
                    'orgId' => $this->organizationId,
                    'Content-Type' => 'application/json'
                ]);

            $url = $this->apiBaseUrl . $endpoint;

            switch (strtoupper($method)) {
                case 'GET':
                    $httpResponse = $response->get($url, $data);
                    break;
                case 'POST':
                    $httpResponse = $response->post($url, $data);
                    break;
                case 'PUT':
                    $httpResponse = $response->put($url, $data);
                    break;
                case 'DELETE':
                    $httpResponse = $response->delete($url, $data);
                    break;
                default:
                    return ['success' => false, 'error' => 'Invalid HTTP method'];
            }

            if ($httpResponse->successful()) {
                return [
                    'success' => true,
                    'data' => $httpResponse->json()
                ];
            }

            Log::error('Zoho Bookings API request failed', [
                'method' => $method,
                'endpoint' => $endpoint,
                'status' => $httpResponse->status(),
                'response' => $httpResponse->body()
            ]);

            return [
                'success' => false,
                'error' => 'API request failed',
                'status' => $httpResponse->status(),
                'response' => $httpResponse->json()
            ];

        } catch (\Exception $e) {
            Log::error('Exception in Zoho Bookings API request', [
                'method' => $method,
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
        $endpoint = "/services/{$serviceId}/freeslots";
        $params = [
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
        $endpoint = '/bookings';
        
        // Format booking data for Zoho API
        $zohoBookingData = [
            'service_id' => $bookingData['service_id'],
            'staff_id' => $bookingData['staff_id'] ?? null,
            'from_time' => $bookingData['appointment_datetime'],
            'customer_details' => [
                'name' => $bookingData['customer_name'],
                'email' => $bookingData['customer_email'],
                'phone' => $bookingData['customer_phone'] ?? null
            ],
            'additional_info' => $bookingData['notes'] ?? '',
            'timezone' => config('app.timezone', 'UTC')
        ];

        $result = $this->makeApiRequest('POST', $endpoint, $zohoBookingData);

        if ($result['success']) {
            Log::info('Zoho booking created successfully', [
                'booking_id' => $result['data']['booking_id'] ?? null,
                'customer' => $bookingData['customer_name']
            ]);
        }

        return $result;
    }

    /**
     * Update a booking in Zoho Bookings
     */
    public function updateBooking(string $bookingId, array $updateData): array
    {
        $endpoint = "/bookings/{$bookingId}";
        return $this->makeApiRequest('PUT', $endpoint, $updateData);
    }

    /**
     * Cancel a booking in Zoho Bookings
     */
    public function cancelBooking(string $bookingId, string $reason = ''): array
    {
        $endpoint = "/bookings/{$bookingId}/cancel";
        $data = [
            'reason' => $reason,
            'send_notification' => true
        ];

        return $this->makeApiRequest('POST', $endpoint, $data);
    }

    /**
     * Get booking details from Zoho Bookings
     */
    public function getBooking(string $bookingId): array
    {
        $endpoint = "/bookings/{$bookingId}";
        return $this->makeApiRequest('GET', $endpoint);
    }

    /**
     * Get all bookings with filters
     */
    public function getBookings(array $filters = []): array
    {
        $endpoint = '/bookings';
        return $this->makeApiRequest('GET', $endpoint, $filters);
    }

    /**
     * Sync therapist data with Zoho Bookings services
     */
    public function syncTherapistWithZoho(array $therapistData): array
    {
        $serviceData = [
            'name' => $therapistData['name'],
            'description' => $therapistData['bio'],
            'duration' => 60, // 1 hour sessions
            'price' => $therapistData['hourly_rate'],
            'currency' => 'NGN', // Nigerian Naira
            'is_active' => $therapistData['status'] === 'active',
            'category' => 'Therapy Sessions'
        ];

        // Check if service already exists
        if (isset($therapistData['zoho_service_id'])) {
            return $this->makeApiRequest('PUT', "/services/{$therapistData['zoho_service_id']}", $serviceData);
        } else {
            return $this->makeApiRequest('POST', '/services', $serviceData);
        }
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
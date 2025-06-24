<?php

namespace App\Traits;

use App\Services\ZohoBookingsService;
use App\Models\TherapistBooking;
use App\Models\Therapist;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

trait ZohoBookingsIntegration
{
    protected $zohoBookingsService;

    /**
     * Get Zoho Bookings service instance
     */
    protected function getZohoBookingsService(): ZohoBookingsService
    {
        if (!$this->zohoBookingsService) {
            $this->zohoBookingsService = app(ZohoBookingsService::class);
        }
        return $this->zohoBookingsService;
    }

    /**
     * Check if Zoho Bookings is enabled and configured
     */
    protected function isZohoBookingsEnabled(): bool
    {
        $enabled = config('services.zoho_bookings.enabled') && 
               $this->getZohoBookingsService()->isConfigured();
               
        Log::info('Zoho Bookings availability check', [
            'enabled_config' => config('services.zoho_bookings.enabled'),
            'is_configured' => $this->getZohoBookingsService()->isConfigured(),
            'final_enabled' => $enabled,
            'config_status' => $this->getZohoBookingsService()->getStatus()
        ]);
        
        return $enabled;
    }

    /**
     * Create booking in both local database and Zoho Bookings
     */
    protected function createBookingWithZoho(array $validatedData, Therapist $therapist, string $paymentReference = null): array
    {
        $user = Auth::user();
        $localBooking = null;
        $zohoBookingId = null;
        
        // If Zoho Bookings is enabled, try creating the booking there first
        if ($this->isZohoBookingsEnabled()) {
            // Create a temporary booking object for Zoho (without saving to DB yet)
            $tempBooking = new TherapistBooking([
                'user_id' => $user->id,
                'therapist_id' => $therapist->id,
                'appointment_datetime' => $validatedData['appointment_datetime'],
                'session_type' => $validatedData['session_type'],
                'platform' => $validatedData['platform'] ?? null,
                'amount' => $therapist->hourly_rate
            ]);
            
            $zohoResult = $this->createZohoBooking($tempBooking, $therapist, $user);
            
            if (!$zohoResult['success']) {
                Log::error('Failed to create booking in Zoho Bookings', [
                    'therapist_id' => $therapist->id,
                    'user_id' => $user->id,
                    'error' => $zohoResult['error']
                ]);
                
                // If Zoho is critical for your business, fail the entire booking
                // Otherwise, proceed with local booking only
                $zohoFailureAction = config('services.zoho_bookings.failure_action', 'continue');
                
                if ($zohoFailureAction === 'fail') {
                    return [
                        'success' => false,
                        'error' => 'Unable to book appointment in scheduling system: ' . $zohoResult['error']
                    ];
                }
            } else {
                $zohoBookingId = $zohoResult['zoho_booking_id'];
            }
        }
        
        // Create local booking
        $localBooking = TherapistBooking::create([
            'user_id' => $user->id,
            'therapist_id' => $therapist->id,
            'appointment_datetime' => $validatedData['appointment_datetime'],
            'session_type' => $validatedData['session_type'],
            'platform' => $validatedData['platform'] ?? null,
            'status' => 'pending',
            'payment_reference' => $paymentReference,
            'amount' => $therapist->hourly_rate,
            'zoho_booking_id' => $zohoBookingId,
            'zoho_last_sync' => $zohoBookingId ? now() : null
        ]);

        Log::info('Booking created successfully', [
            'local_booking_id' => $localBooking->id,
            'platform' => $localBooking->platform,
            'session_type' => $localBooking->session_type,
            'user_message' => $localBooking->user_message,
            'zoho_booking_id' => $zohoBookingId,
            'zoho_enabled' => $this->isZohoBookingsEnabled()
        ]);

        return [
            'success' => true,
            'booking' => $localBooking,
            'zoho_created' => $zohoBookingId !== null
        ];
    }

    /**
     * Create booking in Zoho Bookings
     */
    private function createZohoBooking(TherapistBooking $booking, Therapist $therapist, $user): array
    {
        $serviceId = $this->getOrCreateZohoService($therapist);
        $staffId = $this->ensureTherapistAsStaff($therapist);
        
        // If no service ID or staff ID is available, skip Zoho integration
        if (!$serviceId || !$staffId) {
            Log::info('Skipping Zoho booking creation - missing service or staff ID', [
                'therapist_id' => $therapist->id,
                'booking_id' => $booking->id,
                'service_id' => $serviceId,
                'staff_id' => $staffId
            ]);
            
            return [
                'success' => false,
                'error' => 'Missing Zoho service or staff ID'
            ];
        }

        $zohoService = $this->getZohoBookingsService();

        // Prepare booking data for Zoho Bookings API
        $customerDetails = [
            'name' => $user->name,
            'email' => $user->email
        ];
        
        // Always include phone_number as Zoho requires it
        if (!empty($user->phone) && trim($user->phone) !== '') {
            $customerDetails['phone_number'] = trim($user->phone);
            Log::info('Including user phone number in Zoho booking', ['phone' => $user->phone]);
        } else {
            // Use dummy phone number when user doesn't have one
            $customerDetails['phone_number'] = '+1234567890';
            Log::info('Using dummy phone number for Zoho booking', ['user_phone' => $user->phone ?? 'NULL']);
        }

        $bookingData = [
            'service_id' => $serviceId,
            'staff_id' => $staffId,
            'from_time' => \Carbon\Carbon::parse($booking->appointment_datetime)->format('d-M-Y H:i:s'),
            'timezone' => config('app.timezone', 'UTC'),
            'customer_details' => json_encode($customerDetails),
            'notes' => ''
        ];

        Log::info('Creating Zoho booking with data', [
            'booking_data' => $bookingData,
            'local_booking_id' => $booking->id,
            'original_datetime' => $booking->appointment_datetime,
            'formatted_datetime' => \Carbon\Carbon::parse($booking->appointment_datetime)->format('d-M-Y H:i:s'),
            'carbon_parsed' => \Carbon\Carbon::parse($booking->appointment_datetime)->toISOString()
        ]);

        $result = $zohoService->createBooking($bookingData);
        
        if ($result['success'] && isset($result['data']['response']['returnvalue']['booking_id'])) {
            $zohoBookingId = $result['data']['response']['returnvalue']['booking_id'];
            
            // Update local booking with Zoho booking ID
            $booking->update([
                'zoho_booking_id' => $zohoBookingId,
                'zoho_last_sync' => now()
            ]);
            
            Log::info('Successfully created Zoho booking', [
                'local_booking_id' => $booking->id,
                'zoho_booking_id' => $zohoBookingId
            ]);
            
            return [
                'success' => true,
                'zoho_booking_id' => $zohoBookingId
            ];
        }

        Log::error('Failed to create Zoho booking', [
            'local_booking_id' => $booking->id,
            'error' => $result['error'] ?? 'Unknown error',
            'response' => $result['data'] ?? null
        ]);

        return [
            'success' => false,
            'error' => $result['error'] ?? 'Failed to create Zoho booking'
        ];
    }

    /**
     * Get or create Zoho service for therapist and ensure staff exists
     */
    private function getOrCreateZohoService(Therapist $therapist): ?string
    {
        // Use configured service ID or default one
        if ($therapist->zoho_service_id) {
            return $therapist->zoho_service_id;
        }

        // Use default service ID from config or hardcoded
        $defaultServiceId = config('services.zoho_bookings.default_service_id', '4777370000000046052');
        
        Log::info('Using default Zoho service ID for therapist', [
            'therapist_id' => $therapist->id,
            'service_id' => $defaultServiceId
        ]);

        return $defaultServiceId;
    }

    /**
     * Ensure therapist exists as staff in Zoho Bookings
     */
    private function ensureTherapistAsStaff(Therapist $therapist): ?string
    {
        // Use configured staff ID or default one
        if ($therapist->zoho_staff_id) {
            return $therapist->zoho_staff_id;
        }

        // Use default staff ID from config or hardcoded
        $defaultStaffId = config('services.zoho_bookings.default_staff_id', '4777370000000046014');
        
        // Update therapist with default staff ID for future use
        $therapist->update(['zoho_staff_id' => $defaultStaffId]);
        
        Log::info('Using default Zoho staff ID for therapist', [
            'therapist_id' => $therapist->id,
            'staff_id' => $defaultStaffId
        ]);

        return $defaultStaffId;
    }

    /**
     * Cancel booking in both local database and Zoho Bookings
     */
    protected function cancelBookingWithZoho(TherapistBooking $booking, string $reason = ''): array
    {
        // Cancel in local database
        $booking->update([
            'status' => 'cancelled',
            'cancellation_reason' => $reason,
            'cancelled_at' => now()
        ]);

        // Cancel in Zoho Bookings if it exists there
        if ($booking->zoho_booking_id && $this->isZohoBookingsEnabled()) {
            $zohoService = $this->getZohoBookingsService();
            $zohoResult = $zohoService->cancelBooking($booking->zoho_booking_id, $reason);

            if ($zohoResult['success']) {
                $booking->update([
                    'zoho_last_sync' => now(),
                    'zoho_data' => array_merge($booking->zoho_data ?? [], ['cancelled_in_zoho' => true])
                ]);

                Log::info('Booking cancelled in both local DB and Zoho Bookings', [
                    'booking_id' => $booking->id,
                    'zoho_booking_id' => $booking->zoho_booking_id
                ]);
            } else {
                Log::warning('Failed to cancel booking in Zoho Bookings', [
                    'booking_id' => $booking->id,
                    'zoho_booking_id' => $booking->zoho_booking_id,
                    'error' => $zohoResult['error']
                ]);
            }
        }

        return [
            'success' => true,
            'booking' => $booking,
            'zoho_cancelled' => $this->isZohoBookingsEnabled() && $booking->zoho_booking_id
        ];
    }

    /**
     * Get available time slots with Zoho integration
     */
    protected function getAvailableSlotsWithZoho(Therapist $therapist, string $date): array
    {
        // Start with local availability
        $localSlots = $this->parseAvailableSlots($therapist->availability);

        // If Zoho is enabled and therapist has Zoho service, get real-time availability
        if ($this->isZohoBookingsEnabled() && $therapist->zoho_service_id) {
            $zohoService = $this->getZohoBookingsService();
            $zohoSlots = $zohoService->getAvailableSlots($therapist->zoho_service_id, $date);

            if ($zohoSlots['success']) {
                // Merge Zoho slots with local slots
                $availableSlots = $zohoSlots['data']['available_slots'] ?? [];
                
                Log::info('Retrieved real-time availability from Zoho', [
                    'therapist_id' => $therapist->id,
                    'date' => $date,
                    'slots_count' => count($availableSlots)
                ]);

                return $availableSlots;
            }
        }

        // Fall back to local availability
        return $localSlots['all_slots'] ?? [];
    }

    /**
     * Sync booking status from Zoho Bookings
     */
    protected function syncBookingFromZoho(TherapistBooking $booking): bool
    {
        if (!$booking->zoho_booking_id || !$this->isZohoBookingsEnabled()) {
            return false;
        }

        $zohoService = $this->getZohoBookingsService();
        $result = $zohoService->getBooking($booking->zoho_booking_id);

        if ($result['success']) {
            $zohoBooking = $result['data'];
            
            // Update local booking with Zoho data
            $booking->update([
                'status' => $this->mapZohoStatusToLocal($zohoBooking['status']),
                'meeting_link' => $zohoBooking['meeting_link'] ?? $booking->meeting_link,
                'zoho_data' => $zohoBooking,
                'zoho_last_sync' => now()
            ]);

            Log::info('Synced booking from Zoho Bookings', [
                'booking_id' => $booking->id,
                'zoho_status' => $zohoBooking['status'],
                'local_status' => $booking->status
            ]);

            return true;
        }

        return false;
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
} 
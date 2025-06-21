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
        return config('services.zoho_bookings.enabled') && 
               $this->getZohoBookingsService()->isConfigured();
    }

    /**
     * Create booking in both local database and Zoho Bookings
     */
    protected function createBookingWithZoho(array $validatedData, Therapist $therapist): array
    {
        $user = Auth::user();
        
        // Create local booking first
        $localBooking = TherapistBooking::create([
            'user_id' => $user->id,
            'therapist_id' => $therapist->id,
            'appointment_datetime' => $validatedData['appointment_datetime'],
            'session_type' => $validatedData['session_type'],
            'platform' => $validatedData['platform'] ?? null,
            'user_message' => $validatedData['user_message'] ?? null,
            'status' => 'pending'
        ]);

        // If Zoho Bookings is enabled, create booking there too
        if ($this->isZohoBookingsEnabled()) {
            $zohoResult = $this->createZohoBooking($localBooking, $therapist, $user);
            
            if ($zohoResult['success']) {
                // Update local booking with Zoho details
                $localBooking->update([
                    'zoho_booking_id' => $zohoResult['zoho_booking_id'],
                    'zoho_data' => $zohoResult['zoho_data'],
                    'zoho_last_sync' => now()
                ]);

                Log::info('Booking created in both local DB and Zoho Bookings', [
                    'local_booking_id' => $localBooking->id,
                    'zoho_booking_id' => $zohoResult['zoho_booking_id']
                ]);
            } else {
                Log::warning('Failed to create booking in Zoho Bookings, but local booking created', [
                    'local_booking_id' => $localBooking->id,
                    'zoho_error' => $zohoResult['error']
                ]);
            }
        }

        return [
            'success' => true,
            'booking' => $localBooking,
            'zoho_created' => $this->isZohoBookingsEnabled()
        ];
    }

    /**
     * Create booking in Zoho Bookings
     */
    private function createZohoBooking(TherapistBooking $booking, Therapist $therapist, $user): array
    {
        $zohoService = $this->getZohoBookingsService();

        // Prepare booking data for Zoho
        $bookingData = [
            'service_id' => $therapist->zoho_service_id ?? $this->getOrCreateZohoService($therapist),
            'staff_id' => $therapist->zoho_staff_id,
            'appointment_datetime' => $booking->appointment_datetime->toISOString(),
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'customer_phone' => $user->phone ?? null,
            'notes' => $booking->user_message ?? ''
        ];

        $result = $zohoService->createBooking($bookingData);

        if ($result['success']) {
            return [
                'success' => true,
                'zoho_booking_id' => $result['data']['booking_id'],
                'zoho_data' => $result['data']
            ];
        }

        return [
            'success' => false,
            'error' => $result['error'] ?? 'Unknown error'
        ];
    }

    /**
     * Get or create Zoho service for therapist
     */
    private function getOrCreateZohoService(Therapist $therapist): ?string
    {
        if ($therapist->zoho_service_id) {
            return $therapist->zoho_service_id;
        }

        // Auto-create service in Zoho for therapist
        $zohoService = $this->getZohoBookingsService();
        
        $therapistData = [
            'name' => $therapist->name . ' - Therapy Session',
            'bio' => $therapist->bio,
            'hourly_rate' => $therapist->hourly_rate,
            'status' => $therapist->status
        ];

        $result = $zohoService->syncTherapistWithZoho($therapistData);
        
        if ($result['success']) {
            $serviceId = $result['data']['service_id'];
            
            // Update therapist with Zoho service ID
            $therapist->update([
                'zoho_service_id' => $serviceId,
                'zoho_last_sync' => now()
            ]);

            Log::info('Created Zoho service for therapist', [
                'therapist_id' => $therapist->id,
                'zoho_service_id' => $serviceId
            ]);

            return $serviceId;
        }

        Log::error('Failed to create Zoho service for therapist', [
            'therapist_id' => $therapist->id,
            'error' => $result['error'] ?? 'Unknown error'
        ]);

        return null;
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
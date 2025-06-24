<?php

namespace App\Http\Controllers;

use App\Models\Therapist;
use App\Models\TherapistBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use App\Notifications\TherapistBookingCancelled;
use App\Notifications\TherapistBookingReminder;
use App\Traits\ZohoBookingsIntegration;

class TherapistBookingController extends Controller
{
    use ZohoBookingsIntegration;
    /**
     * Display available therapists.
     */
    public function index(Request $request)
    {
        $query = Therapist::where('status', 'active');

        // Add search functionality
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('bio', 'like', "%{$searchTerm}%")
                  ->orWhere('specializations', 'like', "%{$searchTerm}%");
            });
        }

        $therapists = $query->withCount(['bookings', 'confirmedBookings'])
            ->with(['bookings' => function($query) {
                $query->where('status', 'completed');
            }])
            ->get()
            ->map(function ($therapist) {
                // Calculate real metrics from actual bookings
                $completedBookings = $therapist->bookings()->where('status', 'completed')->count();
                $totalBookings = $therapist->bookings_count;
                
                // Calculate average rating from completed bookings (can add a reviews table later)
                $avgRating = $completedBookings > 0 ? (4.0 + ($completedBookings % 10) / 10) : 4.5;
                
                // Parse real availability data
                $availabilityData = $this->parseAvailability($therapist->availability);
                
                // Get available time slots from real availability data
                $availableSlots = $this->parseAvailableSlots($therapist->availability);
                
                return [
                    'id' => $therapist->id,
                    'name' => $therapist->name,
                    'bio' => $therapist->bio,
                    'photo_url' => $therapist->photo_url,
                    'specializations' => is_string($therapist->specializations) 
                        ? json_decode($therapist->specializations, true) 
                        : $therapist->specializations,
                    'degree' => $therapist->degree,
                    'years_of_experience' => $therapist->years_of_experience,
                    'hourly_rate' => $therapist->hourly_rate,
                    'availability' => $therapist->availability,
                    'phone' => $therapist->phone,
                    'email' => $therapist->email,
                    'languages' => $therapist->languages,
                    'status' => $therapist->status,
                    'additional_info' => $therapist->additional_info,
                    
                    // Calculated fields
                    'total_bookings' => $totalBookings,
                    'confirmed_bookings' => $therapist->confirmed_bookings_count,
                    'avg_rating' => round($avgRating, 1),
                    'total_reviews' => max($completedBookings, $totalBookings), // Use actual booking count
                    'session_duration' => $availabilityData['session_duration'],
                    'working_hours' => $availabilityData['working_hours'],
                    'schedule_by_day' => $availabilityData['schedule_by_day'],
                    'available_slots' => $availableSlots['all_slots'] ?? [],
                    'slots_by_day' => $availableSlots['slots_by_day'] ?? [],
                ];
            });

        return Inertia::render('Therapists/Index', [
            'therapists' => $therapists,
            'filters' => [
                'search' => $request->search ?? '',
            ],
        ]);
    }

    /**
     * Parse availability data from database
     */
    private function parseAvailability($availability)
    {
        // Decode JSON string if it's a string
        if (is_string($availability)) {
            $availabilityArray = json_decode($availability, true);
        } else {
            $availabilityArray = $availability;
        }
        
        if (!is_array($availabilityArray) || empty($availabilityArray)) {
            return [
                'session_duration' => '90 min',
                'working_hours' => '8AM-8PM',
                'schedule_by_day' => []
            ];
        }
        
        // Group availability by day
        $scheduleByDay = [];
        $allTimes = [];
        
        foreach ($availabilityArray as $slot) {
            // Parse format: "Monday-09:00-10:00"
            if (strpos($slot, '-') !== false) {
                $parts = explode('-', $slot);
                if (count($parts) >= 3) {
                    $day = $parts[0];
                    $startTime = $parts[1];
                    $endTime = $parts[2];
                    
                    if (!isset($scheduleByDay[$day])) {
                        $scheduleByDay[$day] = [];
                    }
                    
                    $scheduleByDay[$day][] = [
                        'start' => $startTime,
                        'end' => $endTime
                    ];
                    
                    $allTimes[] = $startTime;
                    $allTimes[] = $endTime;
                }
            }
        }
        
        // Determine session duration (1 hour based on the time slot format)
        $sessionDuration = '60 min';
        
        // Determine overall working hours range
        $workingHours = '8AM-8PM';
        if (!empty($allTimes)) {
            $minTime = min($allTimes);
            $maxTime = max($allTimes);
            $workingHours = date('gA', strtotime($minTime)) . '-' . date('gA', strtotime($maxTime));
        }
        
        return [
            'session_duration' => $sessionDuration,
            'working_hours' => $workingHours,
            'schedule_by_day' => $scheduleByDay
        ];
    }

    /**
     * Parse available time slots from real availability data
     */
    private function parseAvailableSlots($availability)
    {
        // Decode JSON string if it's a string
        if (is_string($availability)) {
            $availabilityArray = json_decode($availability, true);
        } else {
            $availabilityArray = $availability;
        }
        
        if (!is_array($availabilityArray) || empty($availabilityArray)) {
            return ['09:00', '10:30', '12:00', '13:30', '15:00', '16:30'];
        }
        
        $slotsByDay = [];
        $allSlots = [];
        
        foreach ($availabilityArray as $slot) {
            // Parse format: "Monday-09:00-10:00"
            if (strpos($slot, '-') !== false) {
                $parts = explode('-', $slot);
                if (count($parts) >= 3) {
                    $day = $parts[0];
                    $startTime = $parts[1];
                    
                    if (!isset($slotsByDay[$day])) {
                        $slotsByDay[$day] = [];
                    }
                    
                    $slotsByDay[$day][] = $startTime;
                    $allSlots[] = $startTime;
                }
            }
        }
        
        // Remove duplicates and sort all slots
        $allSlots = array_unique($allSlots);
        sort($allSlots);
        
        return [
            'all_slots' => $allSlots,
            'slots_by_day' => $slotsByDay
        ];
    }

    /**
     * Show therapist details and booking form.
     */
    public function show($id)
    {
        $therapist = Therapist::where('status', 'active')->findOrFail($id);
        
        // Calculate real metrics from actual bookings
        $completedBookings = $therapist->bookings()->where('status', 'completed')->count();
        $totalBookings = $therapist->bookings()->count();
        
        // Calculate average rating from completed bookings
        $avgRating = $completedBookings > 0 ? (4.0 + ($completedBookings % 10) / 10) : 4.5;
        
        // Parse real availability data
        $availabilityData = $this->parseAvailability($therapist->availability);
        
        // Get available time slots with Zoho integration (real-time if configured)
        $todayDate = now()->format('Y-m-d');
        $availableSlots = $this->parseAvailableSlots($therapist->availability);
        
        // If Zoho is enabled, get real-time availability for today
        if ($this->isZohoBookingsEnabled() && $therapist->zoho_service_id) {
            $zohoSlots = $this->getAvailableSlotsWithZoho($therapist, $todayDate);
            if (!empty($zohoSlots)) {
                $availableSlots['zoho_slots'] = $zohoSlots;
                $availableSlots['has_zoho_integration'] = true;
            }
        }
        
        // Format therapist data
        $formattedTherapist = [
            'id' => $therapist->id,
            'name' => $therapist->name,
            'bio' => $therapist->bio,
            'photo_url' => $therapist->photo_url,
            'specializations' => is_string($therapist->specializations) 
                ? json_decode($therapist->specializations, true) 
                : $therapist->specializations,
            'degree' => $therapist->degree,
            'years_of_experience' => $therapist->years_of_experience,
            'hourly_rate' => $therapist->hourly_rate,
            'availability' => $therapist->availability,
            'phone' => $therapist->phone,
            'email' => $therapist->email,
            'languages' => $therapist->languages,
            'status' => $therapist->status,
            'additional_info' => $therapist->additional_info,
            
            // Calculated fields
            'total_bookings' => $totalBookings,
            'avg_rating' => round($avgRating, 1),
            'total_reviews' => max($completedBookings, $totalBookings),
            'session_duration' => $availabilityData['session_duration'],
            'working_hours' => $availabilityData['working_hours'],
            'schedule_by_day' => $availabilityData['schedule_by_day'],
            'available_slots' => $availableSlots['all_slots'] ?? [],
            'slots_by_day' => $availableSlots['slots_by_day'] ?? [],
            'zoho_slots' => $availableSlots['zoho_slots'] ?? [],
            'has_zoho_integration' => $availableSlots['has_zoho_integration'] ?? false,
        ];

        // Get user's existing bookings with this therapist
        $userBookings = TherapistBooking::where('user_id', Auth::id())
            ->where('therapist_id', $id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->with('therapist')
            ->get();

        return Inertia::render('Therapists/Show', [
            'therapist' => $formattedTherapist,
            'userBookings' => $userBookings,
        ]);
    }

    /**
     * Public wrapper for createBookingWithZoho to allow access from other controllers
     */
    public function createBookingForPayment(array $validatedData, Therapist $therapist, string $paymentReference = null): array
    {
        return $this->createBookingWithZoho($validatedData, $therapist, $paymentReference);
    }

    /**
     * Store a new booking and redirect to payment (with Zoho Bookings integration).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'therapist_id' => 'required|exists:therapists,id',
            'appointment_datetime' => [
                'required',
                'date',
                'after:' . now()->addHours(2)->format('Y-m-d H:i:s'), // Minimum 2 hours advance booking
                'before:' . now()->addMonths(3)->format('Y-m-d H:i:s'), // Maximum 3 months ahead
                function ($attribute, $value, $fail) {
                    // Parse datetime with timezone consideration
                    $appointmentTime = \Carbon\Carbon::parse($value, config('app.timezone', 'UTC'));
                    
                    // Check if it's a weekday (Monday-Friday)
                    if ($appointmentTime->isWeekend()) {
                        $fail('Appointments are only available Monday through Friday.');
                    }
                    
                    // Check business hours (8 AM to 8 PM) in local timezone
                    $hour = $appointmentTime->hour;
                    if ($hour < 8 || $hour >= 20) {
                        $fail('Appointments are only available between 8:00 AM and 8:00 PM.');
                    }
                    
                    // Check if appointment is on the hour or half hour
                    $minute = $appointmentTime->minute;
                    if (!in_array($minute, [0, 30])) {
                        $fail('Appointments must be scheduled on the hour or half hour (e.g., 9:00 AM, 9:30 AM).');
                    }
                    
                    // Ensure it's not in the past (with timezone consideration)
                    if ($appointmentTime->isPast()) {
                        $fail('Appointment time cannot be in the past.');
                    }
                }
            ],
            'session_type' => 'required|in:online,in_person',
            'platform' => 'nullable|string',
            'user_message' => 'nullable|string|max:1000',
            'payment_gateway' => 'required|in:paystack,monnify',
        ]);

        // Check if therapist is active
        $therapist = Therapist::where('id', $validated['therapist_id'])
            ->where('status', 'active')
            ->firstOrFail();

        // Use database locking to prevent concurrent bookings for the same slot
        $lockKey = 'booking_lock_' . $validated['therapist_id'] . '_' . \Carbon\Carbon::parse($validated['appointment_datetime'])->format('Y-m-d_H-i');
        
        $lock = Cache::lock($lockKey, 60); // Lock for 60 seconds
        
        if (!$lock->get()) {
            return redirect()->back()->with('error', 'Someone else is currently booking this time slot. Please try again in a moment.');
        }

        try {
            // Check for conflicting bookings (local database) within the lock
            $conflictingBooking = TherapistBooking::where('therapist_id', $validated['therapist_id'])
                ->where('appointment_datetime', $validated['appointment_datetime'])
                ->whereIn('status', ['pending', 'confirmed'])
                ->exists();

            if ($conflictingBooking) {
                return redirect()->back()->with('error', 'This time slot is already booked. Please choose another time.');
            }

            // Check Zoho availability if integration is enabled
            if ($this->isZohoBookingsEnabled()) {
                try {
                    $appointmentDate = \Carbon\Carbon::parse($validated['appointment_datetime'])->format('Y-m-d');
                    $zohoService = $this->getZohoBookingsService();
                    $availabilityResult = $zohoService->getAvailableSlots($therapist->zoho_service_id, $appointmentDate);
                    
                    if ($availabilityResult['success']) {
                        $availableSlots = $availabilityResult['data']['slots'] ?? [];
                        $requestedTime = \Carbon\Carbon::parse($validated['appointment_datetime'])->format('H:i');
                        
                        $slotAvailable = collect($availableSlots)->contains(function ($slot) use ($requestedTime) {
                            return $slot['time'] === $requestedTime && $slot['available'] === true;
                        });
                        
                        if (!$slotAvailable) {
                            return redirect()->back()->with('error', 'This time slot is no longer available in our scheduling system. Please choose another time.');
                        }
                    }
                } catch (\Exception $e) {
                    \Log::warning('Failed to check Zoho availability, proceeding with local check only', [
                        'therapist_id' => $validated['therapist_id'],
                        'datetime' => $validated['appointment_datetime'],
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Generate unique payment reference first
            $reference = 'booking_' . $validated['payment_gateway'] . '_' . time() . '_' . \Illuminate\Support\Str::random(8);
            
            // Use database transaction to ensure data consistency
            $errorMessage = null;
            $redirectUrl = null;
            
            try {
                DB::transaction(function () use ($validated, $therapist, $reference, &$redirectUrl) {
                    // Create booking with Zoho integration
                    $bookingResult = $this->createBookingWithZoho($validated, $therapist, $reference);
                    
                    if (!$bookingResult['success']) {
                        throw new \Exception('Unable to create booking: ' . ($bookingResult['error'] ?? 'Unknown error'));
                    }

                    $booking = $bookingResult['booking'];

                    // Map payment gateway selection to actual service
                    $gateway = $validated['payment_gateway'];

                    // Format data for payment initialization
                    $appointmentDateTime = \Carbon\Carbon::parse($validated['appointment_datetime']);
                    
                    $paymentData = [
                        'therapist_id' => $validated['therapist_id'],
                        'id' => $booking->id, // Use 'id' to match database field
                        'booking_date' => $appointmentDateTime->format('Y-m-d'),
                        'booking_time' => $appointmentDateTime->format('g:i A'),
                        
                        'platform' => $validated['platform'],
                        'session_type' => $validated['session_type'],
                        'payment_gateway' => $gateway,
                        'payment_reference' => $reference
                    ];

                    // Debug log the payment data being sent
                    \Log::info('Sending payment data to PaymentController', [
                        'payment_data' => $paymentData,
                        'booking_id' => $booking->id,
                        'has_id_field' => isset($paymentData['id'])
                    ]);

                    // Initialize payment through PaymentController
                    $paymentController = app(PaymentController::class);
                    $paymentRequest = new Request();
                    $paymentRequest->replace($paymentData); // Use replace to completely set the request data
                    
                    // Debug log to verify request data after replace
                    \Log::info('After replace - PaymentRequest data', [
                        'all_data' => $paymentRequest->all(),
                        'has_id' => $paymentRequest->has('id'),
                        'id_value' => $paymentRequest->input('id'),
                        'original_payment_data' => $paymentData
                    ]);
                    
                    $paymentResponse = $paymentController->initializeTherapistBooking($paymentRequest);

                    if ($paymentResponse->getStatusCode() === 200) {
                        $responseData = json_decode($paymentResponse->getContent(), true);
                        if ($responseData['status']) {
                            $redirectUrl = $responseData['authorization_url'];
                        } else {
                            throw new \Exception('Payment initialization failed: ' . ($responseData['message'] ?? 'Unknown error'));
                        }
                    } else {
                        throw new \Exception('Payment service error: HTTP ' . $paymentResponse->getStatusCode());
                    }
                });
                
                if ($redirectUrl) {
                    return redirect($redirectUrl);
                }
                
            } catch (\Exception $e) {
                // Log the error for debugging
                \Log::error('Booking creation failed', [
                    'user_id' => Auth::id(),
                    'therapist_id' => $validated['therapist_id'],
                    'datetime' => $validated['appointment_datetime'],
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                $errorMessage = 'Unable to process booking: ' . $e->getMessage();
            }
            
            if ($errorMessage) {
                return redirect()->back()->with('error', $errorMessage);
            }

        } finally {
            // Always release the lock
            $lock->release();
        }
    }

    /**
     * Display user's bookings with filtering.
     */
    public function userBookings(Request $request)
    {
        $userId = Auth::id();
        
        // Get booking statistics for filter badges
        $stats = [
            'total' => TherapistBooking::where('user_id', $userId)->count(),
            'pending' => TherapistBooking::where('user_id', $userId)->where('status', 'pending')->count(),
            'confirmed' => TherapistBooking::where('user_id', $userId)->where('status', 'confirmed')->count(),
            'completed' => TherapistBooking::where('user_id', $userId)->where('status', 'completed')->count(),
            'cancelled' => TherapistBooking::where('user_id', $userId)->where('status', 'cancelled')->count(),
            'paid' => TherapistBooking::where('user_id', $userId)->where('payment_status', 'paid')->count(),
        ];

        // Build query with filters - optimize with eager loading
        $query = TherapistBooking::where('user_id', $userId)->with(['therapist:id,name,photo,hourly_rate']);

        // Filter by status
        if ($request->has('status') && !empty($request->status)) {
            if ($request->status === 'upcoming') {
                $query->where('status', 'confirmed')
                      ->where('appointment_datetime', '>', now());
            } elseif ($request->status === 'past') {
                $query->whereIn('status', ['completed', 'cancelled']);
            } elseif ($request->status === 'paid') {
                $query->where('payment_status', 'paid');
            } else {
                $query->where('status', $request->status);
            }
        }

        // Filter by payment status
        if ($request->has('payment_status') && !empty($request->payment_status)) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by date range
        if ($request->has('date_from') && !empty($request->date_from)) {
            $query->whereDate('appointment_datetime', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && !empty($request->date_to)) {
            $query->whereDate('appointment_datetime', '<=', $request->date_to);
        }

        // Search by therapist name
        if ($request->has('search') && !empty($request->search)) {
            $query->whereHas('therapist', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        $bookings = $query->orderBy('appointment_datetime', 'desc')->paginate(10);

        return Inertia::render('Therapists/BookingDetails', [
            'bookings' => $bookings,
            'stats' => $stats,
            'filters' => [
                'status' => $request->status ?? '',
                'payment_status' => $request->payment_status ?? '',
                'date_from' => $request->date_from ?? '',
                'date_to' => $request->date_to ?? '',
                'search' => $request->search ?? '',
            ],
        ]);
    }

    /**
     * Cancel a booking (with Zoho Bookings integration).
     */
    public function cancel(Request $request, $id)
    {
        // Add extra authorization verification
        $userId = Auth::id();
        
        $booking = TherapistBooking::with(['user', 'therapist'])
            ->where('id', $id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->firstOrFail();
            
        // Verify ownership with explicit check
        if ($booking->user_id !== $userId) {
            abort(403, 'Unauthorized: You can only cancel your own bookings.');
        }

        $validated = $request->validate([
            'cancellation_reason' => 'nullable|string|max:500',
        ]);

        // Determine if refund should be issued (e.g., for paid bookings cancelled more than 24 hours in advance)
        $refundIssued = false;
        if ($booking->payment_status === 'paid' && $booking->appointment_datetime->diffInHours(now()) > 24) {
            $refundIssued = true;
            // Here you would typically call your payment service to process the refund
            // For now, just update the payment status
            $booking->update(['payment_status' => 'refunded']);
        }

        // Cancel booking with Zoho integration
        $cancelResult = $this->cancelBookingWithZoho($booking, $validated['cancellation_reason'] ?? 'Cancelled by user');
        
        if (!$cancelResult['success']) {
            return redirect()->back()->with('error', 'Unable to cancel booking. Please try again.');
        }

        // Send cancellation notification
        $booking->user->notify(new TherapistBookingCancelled(
            $booking, 
            $validated['cancellation_reason'] ?? 'Cancelled by user', 
            $refundIssued
        ));

        $successMessage = 'Booking cancelled successfully.';
        if ($refundIssued) {
            $successMessage .= ' A refund has been initiated.';
        }
        if ($cancelResult['zoho_cancelled']) {
            $successMessage .= ' Your booking has also been cancelled in our scheduling system.';
        }

        return redirect()->back()->with('success', $successMessage);
    }

    /**
     * Send session reminders for upcoming bookings
     * This method would typically be called by a scheduled command
     */
    public function sendReminders($reminderType = '24h')
    {
        $timeFilter = match($reminderType) {
            '24h' => [now()->addDay()->startOfHour(), now()->addDay()->endOfHour()],
            '1h' => [now()->addHour()->startOfMinute(), now()->addHour()->addMinutes(5)],
            '15m' => [now()->addMinutes(15)->startOfMinute(), now()->addMinutes(20)],
            default => [now()->addDay()->startOfHour(), now()->addDay()->endOfHour()]
        };

        $upcomingBookings = TherapistBooking::with(['user', 'therapist'])
            ->where('status', 'confirmed')
            ->whereBetween('appointment_datetime', $timeFilter)
            ->get();

        foreach ($upcomingBookings as $booking) {
            $booking->user->notify(new TherapistBookingReminder($booking, $reminderType));
        }

        return response()->json([
            'message' => "Sent {$reminderType} reminders for " . $upcomingBookings->count() . " bookings"
        ]);
    }
}

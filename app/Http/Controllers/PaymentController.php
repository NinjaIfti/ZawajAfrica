<?php

namespace App\Http\Controllers;

use App\Models\TherapistBooking;
use App\Models\User;
use App\Services\PaystackService;
use App\Services\MonnifyService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use App\Notifications\TherapistBookingPaid;
use App\Models\Therapist;

class PaymentController extends Controller
{
    protected $paystackService;
    protected $monnifyService;

    public function __construct(PaystackService $paystackService, MonnifyService $monnifyService)
    {
        $this->paystackService = $paystackService;
        $this->monnifyService = $monnifyService;
        
        // Validate payment gateway configurations
        $this->validatePaymentConfig();
    }
    
    /**
     * Validate that required payment gateway configurations are present
     */
    private function validatePaymentConfig(): void
    {
        $errors = [];
        
        // Check Paystack configuration
        if (empty(config('services.paystack.secret_key'))) {
            $errors[] = 'Paystack secret key not configured';
        }
        if (empty(config('services.paystack.public_key'))) {
            $errors[] = 'Paystack public key not configured';
        }
        
        // Check Monnify configuration
        if (empty(config('services.monnify.secret_key'))) {
            $errors[] = 'Monnify secret key not configured';
        }
        if (empty(config('services.monnify.api_key'))) {
            $errors[] = 'Monnify API key not configured';
        }
        if (empty(config('services.monnify.contract_code'))) {
            $errors[] = 'Monnify contract code not configured';
        }
        
        if (!empty($errors)) {
            Log::critical('Payment gateway configuration errors', ['errors' => $errors]);
            // In production, you might want to disable payment features instead of throwing
            // throw new \Exception('Payment gateway misconfiguration: ' . implode(', ', $errors));
        }
    }

    /**
     * Initialize subscription payment
     */
    public function initializeSubscription(Request $request)
    {
        $request->validate([
            'plan' => 'required|string',
            'agreed_to_terms' => 'required|boolean|accepted'
        ]);

        $user = Auth::user();
        
        // Define subscription plans - Updated pricing
        $plans = [
            'male' => [
                'Basic' => ['price_naira' => 8000],
                'Gold' => ['price_naira' => 15000],
                'Platinum' => ['price_naira' => 25000]
            ],
            'female' => [
                'Basic' => ['price_naira' => 8000],
                'Gold' => ['price_naira' => 15000],
                'Platinum' => ['price_naira' => 25000]
            ]
        ];

        $userGender = $user->gender ?? 'male';
        $planDetails = $plans[$userGender][$request->plan] ?? null;

        if (!$planDetails) {
            return back()->withErrors(['plan' => 'Invalid plan selected']);
        }

        $reference = 'sub_' . Str::random(16);
        $amount = $planDetails['price_naira'] * 100; // Convert to kobo

        // Use absolute URL for callback to ensure it works in production
        $callbackUrl = url('/payment/callback');
        
        Log::info('Subscription payment initialization', [
            'user_id' => $user->id,
            'plan' => $request->plan,
            'amount' => $amount,
            'reference' => $reference,
            'callback_url' => $callbackUrl,
            'app_url' => config('app.url'),
            'request_url' => $request->url()
        ]);

        $paymentData = [
            'email' => $user->email,
            'amount' => $amount,
            'reference' => $reference,
            'callback_url' => $callbackUrl,
            'metadata' => [
                'type' => 'subscription',
                'plan' => $request->plan,
                'user_id' => $user->id,
                'user_gender' => $userGender,
                'callback_url' => $callbackUrl // Store callback URL in metadata as backup
            ]
        ];

        $response = $this->paystackService->initializePayment($paymentData);

        if ($response['status'] === true) {
            // Store payment reference in session for verification
            session(['payment_reference' => $reference]);
            
            Log::info('Subscription payment initialized successfully', [
                'reference' => $reference,
                'authorization_url' => $response['data']['authorization_url']
            ]);
            
            return response()->json([
                'status' => true,
                'authorization_url' => $response['data']['authorization_url'],
                'reference' => $reference
            ]);
        }

        Log::error('Subscription payment initialization failed', [
            'user_id' => $user->id,
            'plan' => $request->plan,
            'response' => $response
        ]);

        return response()->json([
            'status' => false,
            'message' => $response['message'] ?? 'Payment initialization failed'
        ], 400);
    }

    /**
     * Initialize therapist booking payment
     */
    public function initializeTherapistBooking(Request $request)
    {
        try {
    
            Log::info('PaymentController received data', [
                'all_data' => $request->all(),
                'has_id' => $request->has('id'),
                'id_value' => $request->input('id', 'NOT_SET'),
                'request_method' => $request->method()
            ]);

            // Check if we have an existing booking ID or need to create a new booking
            if ($request->has('id')) {
                // Validate the request for existing booking
                $validated = $request->validate([
                    'therapist_id' => 'required|exists:therapists,id',
                    'id' => 'required|exists:therapist_bookings,id', // Existing booking with Zoho integration
                    'booking_date' => 'required|date|after_or_equal:today',
                    'booking_time' => 'required|string',
                    'platform' => 'nullable|string',
                    'payment_gateway' => 'required|in:paystack,monnify'
                ]);

                $user = Auth::user();
                
                // Find the existing booking that already has Zoho integration
                $booking = TherapistBooking::with('therapist')
                    ->where('id', $validated['id'])
                    ->where('user_id', $user->id) // Ensure user owns this booking
                    ->where('status', 'pending') // Only process pending bookings
                    ->firstOrFail();
            } else {
                // Create new booking first, then process payment
                $validated = $request->validate([
                    'therapist_id' => 'required|exists:therapists,id',
                    'booking_date' => 'required|date|after_or_equal:today',
                    'booking_time' => 'required|string',
                    
                    'platform' => 'nullable|string',
                    'payment_gateway' => 'required|in:paystack,monnify'
                ]);

                $user = Auth::user();
                
                // Convert booking_date and booking_time to appointment_datetime
                $appointmentDateTime = \Carbon\Carbon::createFromFormat(
                    'Y-m-d g:i A', 
                    $validated['booking_date'] . ' ' . $validated['booking_time']
                );

                // Create the booking with Zoho integration using TherapistBookingController logic
                $therapist = Therapist::where('id', $validated['therapist_id'])
                    ->where('status', 'active')
                    ->firstOrFail();

                // Generate unique payment reference
                $reference = 'booking_' . $validated['payment_gateway'] . '_' . time() . '_' . \Illuminate\Support\Str::random(8);
                
                // Create booking data for Zoho integration
                $bookingData = [
                    'therapist_id' => $validated['therapist_id'],
                    'appointment_datetime' => $appointmentDateTime->format('Y-m-d H:i:s'),
                    'session_type' => 'online',
                    'platform' => $validated['platform'],
                    'payment_gateway' => $validated['payment_gateway'],
                ];

                // Use the TherapistBookingController's Zoho integration
                $bookingController = app(\App\Http\Controllers\TherapistBookingController::class);
                $bookingResult = $bookingController->createBookingForPayment($bookingData, $therapist, $reference);
                
                if (!$bookingResult['success']) {
                    throw new \Exception('Unable to create booking: ' . ($bookingResult['error'] ?? 'Unknown error'));
                }

                $booking = $bookingResult['booking'];
                
                Log::info('Created new booking for payment', [
                    'booking_id' => $booking->id,
                    'therapist_id' => $booking->therapist_id,
                    'appointment_datetime' => $booking->appointment_datetime
                ]);
            }

            $therapist = $booking->therapist;
            
            if (!$therapist || !$therapist->hourly_rate) {
                throw new \Exception('Therapist not found or rate not configured');
            }
            
            $gateway = $validated['payment_gateway'];
            $reference = $booking->payment_reference; // Use existing reference from Zoho booking
            $amount = $therapist->hourly_rate * 100; // Convert to kobo (assuming rate is in Naira)

            // Validate payment amount matches therapist rate
            $expectedAmount = $therapist->hourly_rate;
            if ($request->has('amount') && abs($request->amount - $expectedAmount) > 0.01) {
                throw new \Exception('Payment amount mismatch. Expected: ₦' . number_format($expectedAmount, 2));
            }

            // Update existing booking with payment gateway (don't create new booking)
            $booking->update([
                'payment_gateway' => $gateway,
            ]);

            // Initialize payment based on selected gateway
            if ($gateway === 'monnify') {
                $paymentData = [
                    'amount' => $therapist->hourly_rate, // Monnify expects amount in Naira
                    'customerName' => $user->name,
                    'customerEmail' => $user->email,
                    'paymentReference' => $reference,
                    'paymentDescription' => 'Therapy Session with ' . $therapist->name,
                    'redirectUrl' => route('payment.callback'),
                    'metadata' => [
                        'type' => 'therapist_booking',
                        'booking_id' => $booking->id,
                        'therapist_id' => $booking->therapist_id,
                        'user_id' => $user->id,
                        'gateway' => 'monnify'
                    ]
                ];

                $response = $this->monnifyService->initializePayment($paymentData);
            } else {
                $paymentData = [
                    'email' => $user->email,
                    'amount' => $amount, // Paystack expects amount in kobo
                    'reference' => $reference,
                    'callback_url' => route('payment.callback'),
                    'metadata' => [
                        'type' => 'therapist_booking',
                        'booking_id' => $booking->id,
                        'therapist_id' => $booking->therapist_id,
                        'user_id' => $user->id,
                        'gateway' => 'paystack'
                    ]
                ];

                $response = $this->paystackService->initializePayment($paymentData);
            }

            if ($response['status'] === true) {
                return response()->json([
                    'status' => true,
                    'authorization_url' => $response['data']['authorization_url'],
                    'reference' => $reference,
                    'booking_id' => $booking->id
                ]);
            }

            // If payment initialization fails, mark booking as failed but don't delete it
            $booking->update([
                'status' => 'failed',
                'admin_notes' => 'Payment initialization failed: ' . ($response['message'] ?? 'Unknown error')
            ]);
            
            return response()->json([
                'status' => false,
                'message' => $response['message'] ?? 'Payment initialization failed'
            ], 400);

        } catch (\Exception $e) {
            Log::error('Therapist booking payment initialization error', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'An error occurred while processing your request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle payment callback
     */
    public function handleCallback(Request $request)
    {
        // Monnify sends 'paymentReference', Paystack sends 'reference'
        $reference = $request->query('reference') ?? $request->query('paymentReference');
        
        Log::info('Payment callback received', [
            'reference' => $reference,
            'paystack_reference' => $request->query('reference'),
            'monnify_reference' => $request->query('paymentReference'),
            'all_query_params' => $request->query(),
            'request_url' => $request->fullUrl(),
            'user_agent' => $request->userAgent(),
            'ip' => $request->ip()
        ]);
        
        if (!$reference) {
            Log::error('Payment callback: No reference found in either parameter');
            return redirect()->route('subscription.index')->with('error', 'Payment reference not found. Please contact support if payment was deducted.');
        }

        // Determine gateway from reference
        $gateway = str_contains($reference, 'monnify') ? 'monnify' : 'paystack';
        
        Log::info('Payment callback gateway determined', [
            'reference' => $reference,
            'gateway' => $gateway
        ]);
        
        try {
            // Verify payment based on gateway with retry logic
            $maxRetries = 3;
            $response = null;
            
            for ($i = 0; $i < $maxRetries; $i++) {
                try {
                    if ($gateway === 'monnify') {
                        $response = $this->monnifyService->verifyPayment($reference);
                    } else {
                        $response = $this->paystackService->verifyPayment($reference);
                    }
                    break; // Success, exit retry loop
                } catch (\Exception $e) {
                    Log::warning("Payment verification attempt " . ($i + 1) . " failed", [
                        'reference' => $reference,
                        'gateway' => $gateway,
                        'error' => $e->getMessage()
                    ]);
                    
                    if ($i === $maxRetries - 1) {
                        throw $e; // Re-throw on final attempt
                    }
                    
                    sleep(1); // Wait 1 second before retry
                }
            }

            Log::info('Payment verification response', [
                'reference' => $reference,
                'gateway' => $gateway,
                'verification_response' => $response
            ]);

            if ($response['status'] === true && $response['data']['status'] === 'success') {
                $paymentData = $response['data'];
                $metadata = $paymentData['metadata'] ?? [];

                Log::info('Payment verification successful', [
                    'reference' => $reference,
                    'gateway' => $gateway,
                    'payment_data' => $paymentData,
                    'metadata' => $metadata,
                    'metadata_type' => $metadata['type'] ?? 'no_type'
                ]);

                try {
                    DB::beginTransaction();

                    if (isset($metadata['type']) && $metadata['type'] === 'subscription') {
                        Log::info('Processing subscription payment', [
                            'user_id' => $metadata['user_id'] ?? 'unknown',
                            'plan' => $metadata['plan'] ?? 'unknown'
                        ]);
                        
                        $this->handleSubscriptionPayment($paymentData, $metadata);
                        DB::commit();
                        
                        Log::info('Subscription payment processed successfully, redirecting to subscription page');
                        
                        return redirect()->route('subscription.index')->with([
                            'payment_success' => true,
                            'payment_type' => 'subscription',
                            'message' => 'Your subscription has been activated successfully!'
                        ]);
                        
                    } elseif (isset($metadata['type']) && $metadata['type'] === 'therapist_booking') {
                        Log::info('Processing therapist booking payment', [
                            'booking_id' => $metadata['booking_id'] ?? 'no_booking_id',
                            'metadata' => $metadata
                        ]);
                        $this->handleTherapistBookingPayment($paymentData, $metadata);
                        DB::commit();
                        return redirect()->route('therapists.index')->with([
                            'payment_success' => true,
                            'payment_type' => 'therapist_booking'
                        ]);
                    } else {
                        Log::warning('Payment callback: Unknown payment type or missing metadata', [
                            'reference' => $reference,
                            'gateway' => $gateway,
                            'metadata' => $metadata
                        ]);
                        
                        // For unknown payment types, still commit and redirect to dashboard
                        DB::commit();
                        return redirect()->route('dashboard')->with([
                            'payment_success' => true,
                            'payment_type' => 'general',
                            'message' => 'Payment processed successfully!'
                        ]);
                    }

                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Payment processing error: ' . $e->getMessage(), [
                        'reference' => $reference,
                        'gateway' => $gateway,
                        'metadata' => $metadata,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    
                    // Payment was successful but processing failed
                    // Don't show generic error, show specific message for subscription
                    if (isset($metadata['type']) && $metadata['type'] === 'subscription') {
                        return redirect()->route('subscription.index')->with('error', 'Payment was successful but there was an error activating your subscription. Please contact support with reference: ' . $reference);
                    }
                    
                    return redirect()->route('dashboard')->with('error', 'Payment was successful but there was an error processing your order. Please contact support with reference: ' . $reference);
                }
            } else {
                Log::error('Payment verification failed', [
                    'reference' => $reference,
                    'gateway' => $gateway,
                    'verification_response' => $response,
                    'status' => $response['status'] ?? 'unknown',
                    'data_status' => $response['data']['status'] ?? 'unknown'
                ]);
                
                // Redirect to subscription page for subscription payments
                if (str_starts_with($reference, 'sub_')) {
                    return redirect()->route('subscription.index')->with('error', 'Payment verification failed. If you were charged, please contact support with reference: ' . $reference);
                }
                
                return redirect()->route('dashboard')->with('error', 'Payment verification failed. If you were charged, please contact support with reference: ' . $reference);
            }
        } catch (\Exception $e) {
            Log::error('Payment callback exception', [
                'reference' => $reference,
                'gateway' => $gateway,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Network or service error
            if (str_starts_with($reference, 'sub_')) {
                return redirect()->route('subscription.index')->with('error', 'Unable to verify payment due to network issues. If you were charged, please contact support with reference: ' . $reference);
            }
            
            return redirect()->route('dashboard')->with('error', 'Unable to verify payment due to network issues. If you were charged, please contact support with reference: ' . $reference);
        }
    }

    /**
     * Handle webhook notifications
     */
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $paystackSignature = $request->header('x-paystack-signature');
        $monnifySignature = $request->header('monnify-signature');
        
        // Determine webhook source and verify signature
        if ($paystackSignature) {
            // Paystack webhook
            $computedSignature = hash_hmac('sha512', $payload, config('services.paystack.secret_key'));
            
            if (!hash_equals($computedSignature, $paystackSignature)) {
                Log::warning('Invalid Paystack webhook signature', [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'payload_length' => strlen($payload)
                ]);
                return response('Invalid signature', 400);
            }
        } elseif ($monnifySignature) {
            // Monnify webhook
            $computedSignature = hash_hmac('sha512', $payload, config('services.monnify.secret_key'));
            
            if (!hash_equals($computedSignature, $monnifySignature)) {
                Log::warning('Invalid Monnify webhook signature', [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'payload_length' => strlen($payload)
                ]);
                return response('Invalid signature', 400);
            }
        } else {
            Log::warning('Webhook received without valid signature headers', [
                'ip' => $request->ip(),
                'headers' => $request->headers->all()
            ]);
            return response('No valid signature found', 400);
        }

        $event = json_decode($payload, true);
        
        if (!$event) {
            Log::error('Invalid JSON payload in webhook', [
                'payload' => $payload,
                'ip' => $request->ip()
            ]);
            return response('Invalid JSON', 400);
        }
        
        // Handle different webhook events
        if (isset($event['event']) && $event['event'] === 'charge.success') {
            $this->handleSuccessfulCharge($event['data']);
        } elseif (isset($event['eventType']) && $event['eventType'] === 'SUCCESSFUL_TRANSACTION') {
            // Monnify webhook format
            $this->handleSuccessfulCharge($event['eventData']);
        }

        return response('Webhook handled', 200);
    }

    /**
     * Handle subscription payment
     */
    private function handleSubscriptionPayment($paymentData, $metadata)
    {
        $user = User::find($metadata['user_id']);
        
        if (!$user) {
            Log::error('Subscription payment: User not found', [
                'user_id' => $metadata['user_id'],
                'payment_data' => $paymentData
            ]);
            throw new \Exception('User not found for subscription payment');
        }
        
        Log::info('Updating user subscription', [
            'user_id' => $user->id,
            'old_plan' => $user->subscription_plan,
            'new_plan' => $metadata['plan'],
            'old_status' => $user->subscription_status
        ]);
        
        $expiresAt = now()->addMonth();
        
        // Update user subscription
        $user->update([
            'subscription_plan' => $metadata['plan'],
            'subscription_status' => 'active',
            'subscription_expires_at' => $expiresAt
        ]);

        Log::info('User subscription updated successfully', [
            'user_id' => $user->id,
            'new_plan' => $user->fresh()->subscription_plan,
            'new_status' => $user->fresh()->subscription_status,
            'expires_at' => $user->fresh()->subscription_expires_at
        ]);

        // Send subscription confirmation notification
        try {
            $notification = new \App\Notifications\SubscriptionPurchased(
                $metadata['plan'],
                $paymentData['amount'] / 100,
                $paymentData['reference'],
                $expiresAt->toDateTime()
            );
            
            // Send database notification
            $user->notify($notification);
            
            // Send email via Zoho Mail service separately
            dispatch(function () use ($user, $notification) {
                try {
                    $zohoMailService = app(\App\Services\ZohoMailService::class);
                    if ($zohoMailService->isConfigured()) {
                        $zohoMailService->sendWithSender('admin', $notification->toMail($user), $user);
                        \Log::info("Subscription email sent successfully via Zoho", [
                            'user_id' => $user->id,
                            'user_email' => $user->email
                        ]);
                    } else {
                        \Log::warning("Zoho Mail not configured for subscription notifications");
                    }
                } catch (\Exception $e) {
                    \Log::error("Failed to send subscription email via Zoho", [
                        'user_id' => $user->id,
                        'error' => $e->getMessage()
                    ]);
                }
            })->afterResponse();
            
            Log::info('Subscription confirmation email sent successfully', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'plan' => $metadata['plan'],
                'amount' => $paymentData['amount'] / 100
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send subscription confirmation email', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Don't throw here - we want the subscription to still be processed
        }
    }

    /**
     * Handle therapist booking payment
     */
    private function handleTherapistBookingPayment($paymentData, $metadata)
    {
        // Ensure we have a booking_id
        if (!isset($metadata['booking_id'])) {
            Log::error('No booking_id found in metadata', [
                'metadata' => $metadata,
                'payment_data' => $paymentData
            ]);
            throw new \Exception('Booking ID not found in payment metadata');
        }

        $booking = TherapistBooking::with(['user', 'therapist'])->find($metadata['booking_id']);
        
        if (!$booking) {
            Log::error('Booking not found for payment processing', [
                'booking_id' => $metadata['booking_id'],
                'metadata' => $metadata,
                'payment_reference' => $paymentData['reference'] ?? 'no_reference'
            ]);
            throw new \Exception('Booking not found with ID: ' . $metadata['booking_id']);
        }

        Log::info('Processing therapist booking payment', [
            'booking_id' => $booking->id,
            'user_id' => $booking->user_id,
            'therapist_id' => $booking->therapist_id,
            'current_status' => $booking->status,
            'current_payment_status' => $booking->payment_status,
            'amount' => $paymentData['amount'] / 100,
            'payment_reference' => $paymentData['reference']
        ]);

        // Update booking status
        $booking->update([
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'payment_reference' => $paymentData['reference'],
            'payment_gateway' => $metadata['gateway'] ?? 'unknown'
        ]);

        // Refresh the booking to get updated data
        $booking->fresh();

        Log::info('Booking updated successfully', [
            'booking_id' => $booking->id,
            'new_status' => $booking->status,
            'new_payment_status' => $booking->payment_status,
            'payment_gateway' => $booking->payment_gateway
        ]);

        // Send payment confirmation notification
        try {
            if ($booking->user) {
                $booking->user->notify(new TherapistBookingPaid($booking));
                Log::info('TherapistBookingPaid notification sent successfully', [
                    'booking_id' => $booking->id,
                    'user_id' => $booking->user_id,
                    'user_email' => $booking->user->email,
                    'user_name' => $booking->user->name
                ]);
            } else {
                Log::error('User not found for booking notification', [
                    'booking_id' => $booking->id,
                    'user_id' => $booking->user_id
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send TherapistBookingPaid notification', [
                'booking_id' => $booking->id,
                'user_id' => $booking->user_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Don't throw here - we want the payment to still be processed even if notification fails
        }

        Log::info('Therapist booking payment processed successfully', [
            'booking_id' => $booking->id,
            'therapist_id' => $metadata['therapist_id'] ?? $booking->therapist_id,
            'amount' => $paymentData['amount'] / 100,
            'gateway' => $metadata['gateway'] ?? 'unknown'
        ]);
    }

    /**
     * Handle successful charge from webhook
     */
    private function handleSuccessfulCharge($chargeData)
    {
        $reference = $chargeData['reference'];
        $metadata = $chargeData['metadata'];

        // Additional processing for confirmed payments
        Log::info('Webhook - Successful charge', [
            'reference' => $reference,
            'amount' => $chargeData['amount'] / 100
        ]);
    }
}
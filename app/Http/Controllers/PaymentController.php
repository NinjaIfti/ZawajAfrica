<?php

namespace App\Http\Controllers;

use App\Models\TherapistBooking;
use App\Models\User;
use App\Services\PaystackService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    protected $paystackService;

    public function __construct(PaystackService $paystackService)
    {
        $this->paystackService = $paystackService;
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
        
        // Define subscription plans
        $plans = [
            'male' => [
                'Basic' => ['price_usd' => 5, 'price_naira' => 7000],
                'Economy' => ['price_usd' => 10, 'price_naira' => 12000],
                'VIP' => ['price_usd' => 20, 'price_naira' => 20000]
            ],
            'female' => [
                'Basic' => ['price_usd' => 3, 'price_naira' => 5000],
                'Economy' => ['price_usd' => 7, 'price_naira' => 10000],
                'VIP' => ['price_usd' => 15, 'price_naira' => 18000]
            ]
        ];

        $userGender = $user->gender ?? 'male';
        $planDetails = $plans[$userGender][$request->plan] ?? null;

        if (!$planDetails) {
            return back()->withErrors(['plan' => 'Invalid plan selected']);
        }

        $reference = 'sub_' . Str::random(16);
        $amount = $planDetails['price_naira'] * 100; // Convert to kobo

        $paymentData = [
            'email' => $user->email,
            'amount' => $amount,
            'reference' => $reference,
            'callback_url' => route('payment.callback'),
            'metadata' => [
                'type' => 'subscription',
                'plan' => $request->plan,
                'user_id' => $user->id,
                'user_gender' => $userGender
            ]
        ];

        $response = $this->paystackService->initializePayment($paymentData);

        if ($response['status'] === true) {
            // Store payment reference in session for verification
            session(['payment_reference' => $reference]);
            
            return response()->json([
                'status' => true,
                'authorization_url' => $response['data']['authorization_url'],
                'reference' => $reference
            ]);
        }

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
            // Debug: Log the incoming request data
            Log::info('Therapist booking payment request', [
                'request_data' => $request->all(),
                'user_id' => Auth::id()
            ]);

            // Validate the request
            try {
                $validated = $request->validate([
                    'therapist_id' => 'required|exists:therapists,id',
                    'booking_date' => 'required|date|after_or_equal:today',
                    'booking_time' => 'required|string',
                    'notes' => 'nullable|string|max:500',
                    'platform' => 'nullable|string'
                ]);
                
                Log::info('Validation passed', ['validated_data' => $validated]);
                
            } catch (\Illuminate\Validation\ValidationException $e) {
                Log::error('Validation failed', [
                    'errors' => $e->errors(),
                    'request_data' => $request->all()
                ]);
                throw $e;
            }

            $user = Auth::user();
            
            // Check if therapist exists
            $therapist = \App\Models\Therapist::find($request->therapist_id);
            if (!$therapist) {
                Log::error('Therapist not found', ['therapist_id' => $request->therapist_id]);
                throw new \Exception('Therapist not found');
            }
            
            // Check if therapist has hourly_rate
            if (!$therapist->hourly_rate) {
                Log::error('Therapist hourly rate not set', [
                    'therapist_id' => $request->therapist_id,
                    'hourly_rate' => $therapist->hourly_rate
                ]);
                throw new \Exception('Therapist hourly rate not configured');
            }
            
            Log::info('Therapist found', [
                'therapist_id' => $therapist->id,
                'hourly_rate' => $therapist->hourly_rate
            ]);
            
            $reference = 'booking_' . \Illuminate\Support\Str::random(16);
            $amount = $therapist->hourly_rate * 100; // Convert to kobo (assuming rate is in Naira)

            Log::info('Creating booking record', [
                'user_id' => $user->id,
                'therapist_id' => $request->therapist_id,
                'booking_date' => $request->booking_date,
                'booking_time' => $request->booking_time,
                'amount' => $therapist->hourly_rate,
                'reference' => $reference
            ]);

            // Combine date and time into appointment_datetime
            $appointmentDatetime = Carbon::createFromFormat('Y-m-d g:i A', $request->booking_date . ' ' . $request->booking_time);

            // Create booking record
            $booking = TherapistBooking::create([
                'user_id' => $user->id,
                'therapist_id' => $request->therapist_id,
                'appointment_datetime' => $appointmentDatetime,
                'booking_date' => $request->booking_date,
                'booking_time' => $request->booking_time,
                'notes' => $request->notes,
                'status' => 'pending',
                'amount' => $therapist->hourly_rate,
                'payment_reference' => $reference
            ]);

            Log::info('Booking created successfully', ['booking_id' => $booking->id]);

            $paymentData = [
                'email' => $user->email,
                'amount' => $amount,
                'reference' => $reference,
                'callback_url' => route('payment.callback'),
                'metadata' => [
                    'type' => 'therapist_booking',
                    'booking_id' => $booking->id,
                    'therapist_id' => $request->therapist_id,
                    'user_id' => $user->id
                ]
            ];

            Log::info('Initializing Paystack payment', ['payment_data' => $paymentData]);

            $response = $this->paystackService->initializePayment($paymentData);

            Log::info('Paystack response', ['response' => $response]);

            if ($response['status'] === true) {
                return response()->json([
                    'status' => true,
                    'authorization_url' => $response['data']['authorization_url'],
                    'reference' => $reference,
                    'booking_id' => $booking->id
                ]);
            }

            // Delete the booking if payment initialization fails
            $booking->delete();
            
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
        $reference = $request->query('reference');
        
        if (!$reference) {
            return redirect()->route('dashboard')->with('error', 'Payment reference not found');
        }

        $response = $this->paystackService->verifyPayment($reference);

        if ($response['status'] === true && $response['data']['status'] === 'success') {
            $paymentData = $response['data'];
            $metadata = $paymentData['metadata'];

            try {
                DB::beginTransaction();

                if ($metadata['type'] === 'subscription') {
                    $this->handleSubscriptionPayment($paymentData, $metadata);
                    DB::commit();
                    return redirect()->route('subscription.index')->with('success', 'Subscription activated successfully!');
                    
                } elseif ($metadata['type'] === 'therapist_booking') {
                    $this->handleTherapistBookingPayment($paymentData, $metadata);
                    DB::commit();
                    return redirect()->route('therapists.index')->with('success', 'Booking confirmed successfully!');
                }

                DB::commit();
                return redirect()->route('dashboard')->with('success', 'Payment completed successfully!');

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Payment processing error: ' . $e->getMessage(), [
                    'reference' => $reference,
                    'metadata' => $metadata
                ]);
                return redirect()->route('dashboard')->with('error', 'Payment was successful but there was an error processing your order. Please contact support.');
            }
        }

        return redirect()->route('dashboard')->with('error', 'Payment verification failed');
    }

    /**
     * Handle webhook notifications
     */
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('x-paystack-signature');
        
        // Verify webhook signature
        $computedSignature = hash_hmac('sha512', $payload, config('services.paystack.secret_key'));
        
        if (!hash_equals($computedSignature, $signature)) {
            Log::warning('Invalid webhook signature');
            return response('Invalid signature', 400);
        }

        $event = json_decode($payload, true);
        
        if ($event['event'] === 'charge.success') {
            $this->handleSuccessfulCharge($event['data']);
        }

        return response('Webhook handled', 200);
    }

    /**
     * Handle subscription payment
     */
    private function handleSubscriptionPayment($paymentData, $metadata)
    {
        $user = User::find($metadata['user_id']);
        
        if ($user) {
            // Update user subscription
            $user->update([
                'subscription_plan' => $metadata['plan'],
                'subscription_status' => 'active',
                'subscription_expires_at' => now()->addMonth()
            ]);

            Log::info('Subscription payment processed', [
                'user_id' => $user->id,
                'plan' => $metadata['plan'],
                'amount' => $paymentData['amount'] / 100
            ]);
        }
    }

    /**
     * Handle therapist booking payment
     */
    private function handleTherapistBookingPayment($paymentData, $metadata)
    {
        $booking = TherapistBooking::find($metadata['booking_id']);
        
        if ($booking) {
            $booking->update([
                'status' => 'confirmed',
                'payment_status' => 'paid',
                'payment_reference' => $paymentData['reference']
            ]);

            Log::info('Therapist booking payment processed', [
                'booking_id' => $booking->id,
                'therapist_id' => $metadata['therapist_id'],
                'amount' => $paymentData['amount'] / 100
            ]);
        }
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
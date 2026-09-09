<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\PaystackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function __construct(private readonly PaystackService $paystackService)
    {
    }

    public function initializeSubscription(Request $request)
    {
        $request->validate([
            'plan' => ['required', 'string'],
            'agreed_to_terms' => ['required', 'boolean', 'accepted'],
        ]);

        $user = Auth::user();
        $plans = [
            'Basic' => 8000,
            'Gold' => 15000,
            'Platinum' => 25000,
        ];
        $amount = $plans[$request->string('plan')->toString()] ?? null;
        if ($amount === null) {
            return back()->withErrors(['plan' => 'Invalid plan selected']);
        }

        $reference = 'sub_'.Str::random(16);
        $response = $this->paystackService->initializePayment([
            'email' => $user->email,
            'amount' => $amount * 100,
            'reference' => $reference,
            'callback_url' => url('/payment/callback'),
            'metadata' => [
                'type' => 'subscription',
                'user_id' => $user->id,
                'plan' => $request->string('plan')->toString(),
            ],
        ]);

        if (($response['status'] ?? false) !== true) {
            return back()->with('error', $response['message'] ?? 'Unable to initialize payment.');
        }

        return response()->json($response);
    }

    public function handleCallback(Request $request)
    {
        $reference = $request->query('reference');
        if (!$reference) {
            return redirect()->route('subscription.index')->with('error', 'Payment reference not found.');
        }

        try {
            $response = $this->paystackService->verifyPayment($reference);
            if (($response['status'] ?? false) !== true || ($response['data']['status'] ?? null) !== 'success') {
                return redirect()->route('subscription.index')->with('error', 'Payment verification failed.');
            }

            $paymentData = $response['data'];
            $metadata = $paymentData['metadata'] ?? [];
            if (($metadata['type'] ?? null) !== 'subscription') {
                return redirect()->route('dashboard')->with('error', 'Unsupported payment type.');
            }

            DB::transaction(fn () => $this->handleSubscriptionPayment($paymentData, $metadata));

            return redirect()->route('subscription.index')->with([
                'payment_success' => true,
                'payment_type' => 'subscription',
                'message' => 'Your subscription has been activated successfully!',
            ]);
        } catch (\Throwable $e) {
            Log::error('Subscription payment callback failed', [
                'reference' => $reference,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('subscription.index')->with('error', 'Unable to verify payment. Please contact support.');
        }
    }

    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('x-paystack-signature');
        $expected = hash_hmac('sha512', $payload, (string) config('services.paystack.secret_key'));

        if (!$signature || !hash_equals($expected, $signature)) {
            return response('Invalid signature', 400);
        }

        $event = json_decode($payload, true);
        if (!is_array($event)) {
            return response('Invalid JSON', 400);
        }

        if (($event['event'] ?? null) === 'charge.success') {
            $data = $event['data'] ?? [];
            $metadata = $data['metadata'] ?? [];
            if (($metadata['type'] ?? null) === 'subscription') {
                DB::transaction(fn () => $this->handleSubscriptionPayment($data, $metadata));
            }
        }

        return response('Webhook handled', 200);
    }

    private function handleSubscriptionPayment(array $paymentData, array $metadata): void
    {
        $user = User::findOrFail($metadata['user_id']);
        $user->forceFill([
            'subscription_plan' => $metadata['plan'],
            'subscription_status' => 'active',
            'subscription_expires_at' => now()->addMonth(),
        ])->save();
    }
}

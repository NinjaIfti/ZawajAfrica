<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\Payments\PaymentGatewayManager;
use App\Services\Payments\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Throwable;

class PaymentController extends Controller
{
    public function initializeSubscription(Request $request, PaymentService $payments): JsonResponse
    {
        $data = $request->validate([
            'plan' => ['required', Rule::in(['Basic', 'Gold', 'Platinum'])],
            'gateway' => ['required', Rule::in(['stripe', 'paypal', 'paystack'])],
            'currency' => ['nullable', 'string', 'size:3', 'regex:/^[A-Za-z]{3}$/'],
        ]);
        $amounts = ['Basic' => 8000, 'Gold' => 15000, 'Platinum' => 25000];

        try {
            return response()->json($payments->initializeSubscription(
                $request->user(),
                $data['plan'],
                $data['gateway'],
                strtoupper($data['currency'] ?? config('payments.currency', 'USD')),
                $amounts[$data['plan']],
            ), 201);
        } catch (Throwable $exception) {
            Log::warning('Payment initialization failed', ['error' => $exception->getMessage()]);
            return response()->json(['message' => 'Payment initialization is unavailable for this gateway.'], 422);
        }
    }

    public function verify(Request $request, Payment $payment, PaymentGatewayManager $manager): JsonResponse
    {
        abort_unless($payment->user_id === $request->user()->id || $request->user()->isAdmin(), 403);
        if (!$payment->provider_reference) return response()->json(['message' => 'Payment is not initialized.'], 422);
        try {
            $result = $manager->gateway($payment->provider)->verify($payment->provider_reference);
            return response()->json(['payment' => $payment->fresh(), 'provider' => $result]);
        } catch (Throwable) {
            return response()->json(['message' => 'Payment verification is unavailable.'], 422);
        }
    }

    public function webhook(Request $request, string $gateway, PaymentService $payments): JsonResponse
    {
        abort_unless(array_key_exists($gateway, config('payments.gateways', [])), 404);
        try {
            $result = $payments->handleWebhook($gateway, $request);
            return response()->json(['received' => true, 'duplicate' => $result['duplicate']]);
        } catch (Throwable $exception) {
            Log::warning('Payment webhook rejected', ['gateway' => $gateway, 'error' => $exception->getMessage()]);
            return response()->json(['message' => 'Webhook verification failed.'], 400);
        }
    }
}

<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Models\PaymentEvent;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class PaymentService
{
    public function __construct(private readonly PaymentGatewayManager $manager)
    {
    }

    /** @return array<string,mixed> */
    public function initializeSubscription(User $user, string $plan, string $gateway, string $currency, int $amountMinor): array
    {
        $adapter = $this->manager->gateway($gateway);
        if (!$adapter->enabled()) throw new RuntimeException('This payment gateway is disabled.');

        $reference = 'sub_'.Str::lower(Str::random(24));
        $payment = Payment::create([
            'user_id' => $user->id,
            'provider' => $gateway,
            'reference' => $reference,
            'type' => 'subscription',
            'status' => 'pending',
            'currency' => strtoupper($currency),
            'amount_minor' => $amountMinor,
            'metadata' => ['plan' => $plan, 'user_id' => $user->id, 'type' => 'subscription'],
        ]);

        $result = $adapter->initialize([
            'reference' => $payment->reference,
            'type' => $payment->type,
            'user_id' => $user->id,
            'email' => $user->email,
            'currency' => $payment->currency,
            'amount_minor' => $payment->amount_minor,
            'metadata' => $payment->metadata,
            'return_url' => url('/api/v1/payments/callback'),
            'cancel_url' => url('/subscription'),
        ], $payment->reference);

        if (!$result['ok']) {
            $payment->update(['status' => 'failed']);
            throw new RuntimeException('The payment provider rejected initialization.');
        }

        $providerReference = $this->providerReference($gateway, $result['data']);
        $payment->update(['provider_reference' => $providerReference]);

        return ['reference' => $payment->reference, 'provider' => $gateway, 'data' => $result['data']];
    }

    /** @return array{duplicate:bool,event:PaymentEvent} */
    public function handleWebhook(string $gateway, Request $request): array
    {
        $adapter = $this->manager->gateway($gateway);
        if (!$adapter->verifyWebhook($request)) throw new RuntimeException('Webhook signature verification failed.');
        $normalized = $adapter->normalizeWebhook($request);
        if ($normalized['event_id'] === '') throw new RuntimeException('Webhook event ID is missing.');

        try {
            $event = PaymentEvent::create([
                'provider' => $gateway,
                'event_id' => $normalized['event_id'],
                'event_type' => $normalized['event_type'],
                'payload' => $normalized['payload'],
                'status' => 'received',
            ]);
        } catch (QueryException $exception) {
            if (!$this->isUniqueViolation($exception)) throw $exception;
            return ['duplicate' => true, 'event' => PaymentEvent::where('provider', $gateway)->where('event_id', $normalized['event_id'])->firstOrFail()];
        }

        DB::transaction(function () use ($event, $gateway, $normalized): void {
            $payment = $this->paymentFromEvent($gateway, $normalized['payload']);
            if ($payment && $this->isSuccessfulEvent($gateway, $normalized['event_type'], $normalized['payload'])) {
                $payment = Payment::query()->lockForUpdate()->find($payment->id);
                if ($payment->status !== 'paid') {
                    $payment->update(['status' => 'paid', 'paid_at' => now()]);
                    if ($payment->type === 'subscription') {
                        $payment->user()->update([
                            'subscription_plan' => $payment->metadata['plan'] ?? null,
                            'subscription_status' => 'active',
                            'subscription_expires_at' => now()->addMonth(),
                        ]);
                    }
                }
            }
            $event->update(['status' => 'processed', 'processed_at' => now()]);
        });

        return ['duplicate' => false, 'event' => $event->fresh()];
    }

    private function paymentFromEvent(string $gateway, array $payload): ?Payment
    {
        $data = $payload['data'] ?? $payload['resource'] ?? [];
        $metadata = $data['metadata'] ?? [];
        $reference = $metadata['reference'] ?? $metadata['custom_id'] ?? $data['reference'] ?? null;
        $providerReference = $data['id'] ?? $data['reference'] ?? null;
        if ($gateway === 'paypal') {
            $providerReference = data_get($data, 'supplementary_data.related_ids.order_id', $data['id'] ?? null);
            $reference = $data['purchase_units'][0]['reference_id'] ?? $reference;
        }
        return Payment::query()->when($reference, fn ($q) => $q->where('reference', $reference))
            ->when(!$reference && $providerReference, fn ($q) => $q->where('provider_reference', $providerReference))
            ->first();
    }

    private function isSuccessfulEvent(string $gateway, string $eventType, array $payload): bool
    {
        return match ($gateway) {
            'stripe' => $eventType === 'payment_intent.succeeded',
            'paypal' => in_array($eventType, ['PAYMENT.CAPTURE.COMPLETED', 'CHECKOUT.ORDER.COMPLETED'], true),
            'paystack' => $eventType === 'charge.success' && data_get($payload, 'data.status') === 'success',
            default => false,
        };
    }

    private function providerReference(string $gateway, array $data): ?string
    {
        return match ($gateway) {
            'stripe' => $data['data']['id'] ?? $data['id'] ?? null,
            'paypal' => $data['id'] ?? null,
            'paystack' => $data['data']['reference'] ?? null,
            default => null,
        };
    }

    private function isUniqueViolation(QueryException $exception): bool
    {
        return str_contains(strtolower($exception->getMessage()), 'unique') || str_contains(strtolower($exception->getMessage()), 'duplicate');
    }
}

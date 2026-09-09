<?php

namespace App\Services\Payments;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class StripeGateway implements PaymentGateway
{
    public function __construct(private readonly PaymentGatewayManager $manager)
    {
    }

    public function name(): string { return 'stripe'; }
    public function enabled(): bool { return (bool) $this->manager->effective($this->name())['enabled']; }
    public function mode(): string { return $this->manager->effective($this->name())['mode']; }

    public function initialize(array $payment, string $idempotencyKey): array
    {
        $config = $this->manager->effective($this->name());
        $this->credentials($config);
        $response = Http::withToken($config['secret_key'])
            ->withHeaders(['Idempotency-Key' => $idempotencyKey])
            ->asForm()
            ->post(rtrim($config['base_url'], '/').'/v1/payment_intents', [
                'amount' => $payment['amount_minor'],
                'currency' => strtolower($payment['currency']),
                'metadata[reference]' => $payment['reference'],
                'metadata[type]' => $payment['type'],
                'metadata[user_id]' => $payment['user_id'],
            ]);
        return $this->response($response->json(), $response->successful());
    }

    public function verify(string $providerReference): array
    {
        $config = $this->manager->effective($this->name());
        $this->credentials($config);
        $response = Http::withToken($config['secret_key'])
            ->get(rtrim($config['base_url'], '/').'/v1/payment_intents/'.urlencode($providerReference));
        return $this->response($response->json(), $response->successful());
    }

    public function verifyWebhook(Request $request): bool
    {
        $secret = $this->manager->effective($this->name())['webhook_secret'] ?? null;
        $header = $request->header('Stripe-Signature');
        if (!$secret || !$header) return false;
        $parts = collect(explode(',', $header))->mapWithKeys(function ($part) {
            [$key, $value] = array_pad(explode('=', $part, 2), 2, null);
            return [$key => $value];
        });
        $timestamp = (int) ($parts->get('t') ?? 0);
        $signature = $parts->get('v1');
        if (!$timestamp || !$signature || abs(time() - $timestamp) > 300) return false;
        $expected = hash_hmac('sha256', $timestamp.'.'.$request->getContent(), $secret);
        return hash_equals($expected, $signature);
    }

    public function normalizeWebhook(Request $request): array
    {
        $payload = $request->json()->all();
        return [
            'event_id' => (string) ($payload['id'] ?? ''),
            'event_type' => (string) ($payload['type'] ?? ''),
            'payload' => $payload,
        ];
    }

    private function credentials(array $config): void
    {
        if (!filled($config['secret_key'] ?? null)) throw new RuntimeException('Stripe is not configured.');
    }

    private function response(?array $payload, bool $successful): array
    {
        return ['ok' => $successful, 'data' => $payload ?? []];
    }
}

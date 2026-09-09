<?php

namespace App\Services\Payments;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PaystackGateway implements PaymentGateway
{
    public function __construct(private readonly PaymentGatewayManager $manager)
    {
    }

    public function name(): string { return 'paystack'; }
    public function enabled(): bool { return (bool) $this->manager->effective($this->name())['enabled']; }
    public function mode(): string { return $this->manager->effective($this->name())['mode']; }

    public function initialize(array $payment, string $idempotencyKey): array
    {
        $config = $this->manager->effective($this->name());
        $this->credentials($config);
        $response = Http::withToken($config['secret_key'])
            ->withHeaders(['X-Idempotency-Key' => $idempotencyKey])
            ->post(rtrim($config['base_url'], '/').'/transaction/initialize', [
                'email' => $payment['email'],
                'amount' => $payment['amount_minor'],
                'currency' => strtoupper($payment['currency']),
                'reference' => $payment['reference'],
                'callback_url' => $payment['return_url'] ?? null,
                'metadata' => $payment['metadata'] ?? [],
            ]);
        return ['ok' => $response->successful() && $response->json('status') === true, 'data' => $response->json() ?? []];
    }

    public function verify(string $providerReference): array
    {
        $config = $this->manager->effective($this->name());
        $this->credentials($config);
        $response = Http::withToken($config['secret_key'])
            ->get(rtrim($config['base_url'], '/').'/transaction/verify/'.urlencode($providerReference));
        return ['ok' => $response->successful() && $response->json('status') === true, 'data' => $response->json() ?? []];
    }

    public function verifyWebhook(Request $request): bool
    {
        $secret = $this->manager->effective($this->name())['secret_key'] ?? null;
        $signature = $request->header('x-paystack-signature');
        return filled($secret) && filled($signature) && hash_equals(hash_hmac('sha512', $request->getContent(), $secret), $signature);
    }

    public function normalizeWebhook(Request $request): array
    {
        $payload = $request->json()->all();
        return [
            'event_id' => (string) ($payload['id'] ?? ($payload['data']['reference'] ?? sha1($request->getContent()))),
            'event_type' => (string) ($payload['event'] ?? ''),
            'payload' => $payload,
        ];
    }

    private function credentials(array $config): void
    {
        if (!filled($config['secret_key'] ?? null)) throw new RuntimeException('Paystack is not configured.');
    }
}

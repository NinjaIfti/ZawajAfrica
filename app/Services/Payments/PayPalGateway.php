<?php

namespace App\Services\Payments;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PayPalGateway implements PaymentGateway
{
    public function __construct(private readonly PaymentGatewayManager $manager)
    {
    }

    public function name(): string { return 'paypal'; }
    public function enabled(): bool { return (bool) $this->manager->effective($this->name())['enabled']; }
    public function mode(): string { return $this->manager->effective($this->name())['mode']; }

    public function initialize(array $payment, string $idempotencyKey): array
    {
        $config = $this->manager->effective($this->name());
        $this->credentials($config);
        $response = Http::withToken($this->token($config))
            ->withHeaders(['PayPal-Request-Id' => $idempotencyKey])
            ->post($this->baseUrl($config).'/v2/checkout/orders', [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'reference_id' => $payment['reference'],
                    'amount' => [
                        'currency_code' => strtoupper($payment['currency']),
                        'value' => number_format($payment['amount_minor'] / 100, 2, '.', ''),
                    ],
                ]],
                'application_context' => [
                    'return_url' => $payment['return_url'] ?? null,
                    'cancel_url' => $payment['cancel_url'] ?? null,
                ],
            ]);
        return ['ok' => $response->successful(), 'data' => $response->json() ?? []];
    }

    public function verify(string $providerReference): array
    {
        $config = $this->manager->effective($this->name());
        $this->credentials($config);
        $response = Http::withToken($this->token($config))
            ->get($this->baseUrl($config).'/v2/checkout/orders/'.urlencode($providerReference));
        return ['ok' => $response->successful(), 'data' => $response->json() ?? []];
    }

    public function verifyWebhook(Request $request): bool
    {
        $config = $this->manager->effective($this->name());
        if (!$this->credentialsAvailable($config)) return false;
        $payload = $request->json()->all();
        $verify = Http::withToken($this->token($config))->post($this->baseUrl($config).'/v1/notifications/verify-webhook-signature', [
            'auth_algo' => $request->header('PAYPAL-AUTH-ALGO'),
            'cert_url' => $request->header('PAYPAL-CERT-URL'),
            'transmission_id' => $request->header('PAYPAL-TRANSMISSION-ID'),
            'transmission_sig' => $request->header('PAYPAL-TRANSMISSION-SIG'),
            'transmission_time' => $request->header('PAYPAL-TRANSMISSION-TIME'),
            'webhook_id' => $config['webhook_id'],
            'webhook_event' => $payload,
        ]);
        return $verify->successful() && $verify->json('verification_status') === 'SUCCESS';
    }

    public function normalizeWebhook(Request $request): array
    {
        $payload = $request->json()->all();
        return [
            'event_id' => (string) ($payload['id'] ?? ''),
            'event_type' => (string) ($payload['event_type'] ?? ''),
            'payload' => $payload,
        ];
    }

    private function token(array $config): string
    {
        $response = Http::asForm()->withBasicAuth($config['client_id'], $config['client_secret'])
            ->post($this->baseUrl($config).'/v1/oauth2/token', ['grant_type' => 'client_credentials']);
        if (!$response->successful() || !filled($response->json('access_token'))) throw new RuntimeException('PayPal authentication failed.');
        return $response->json('access_token');
    }

    private function baseUrl(array $config): string
    {
        return rtrim($config[$config['mode'] === 'live' ? 'live_base_url' : 'sandbox_base_url'], '/');
    }

    private function credentialsAvailable(array $config): bool
    {
        return filled($config['client_id'] ?? null) && filled($config['client_secret'] ?? null) && filled($config['webhook_id'] ?? null);
    }

    private function credentials(array $config): void
    {
        if (!$this->credentialsAvailable($config)) throw new RuntimeException('PayPal is not configured.');
    }
}

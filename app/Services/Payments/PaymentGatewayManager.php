<?php

namespace App\Services\Payments;

use App\Models\PaymentGatewaySetting;
use InvalidArgumentException;

class PaymentGatewayManager
{
    public function gateway(string $name): PaymentGateway
    {
        $gateway = match ($name) {
            'stripe' => app(StripeGateway::class),
            'paypal' => app(PayPalGateway::class),
            'paystack' => app(PaystackGateway::class),
            default => throw new InvalidArgumentException('Unsupported payment gateway.'),
        };

        return $gateway;
    }

    /** @return array<string,array<string,mixed>> */
    public function settings(): array
    {
        $rows = PaymentGatewaySetting::query()->get()->keyBy('gateway');
        $result = [];
        foreach (array_keys(config('payments.gateways', [])) as $name) {
            $config = config("payments.gateways.$name", []);
            $row = $rows->get($name);
            $result[$name] = [
                'enabled' => $row?->enabled ?? (bool) ($config['enabled'] ?? false),
                'mode' => $row?->mode ?? ($config['mode'] ?? 'sandbox'),
                'default_currency' => $row?->default_currency ?? config('payments.currency', 'USD'),
                'configured' => $this->hasCredentials($name, $config),
            ];
        }
        return $result;
    }

    public function effective(string $name): array
    {
        $settings = $this->settings()[$name] ?? throw new InvalidArgumentException('Unsupported payment gateway.');
        return [...config("payments.gateways.$name", []), ...$settings];
    }

    private function hasCredentials(string $name, array $config): bool
    {
        return match ($name) {
            'stripe' => filled($config['secret_key'] ?? null) && filled($config['webhook_secret'] ?? null),
            'paypal' => filled($config['client_id'] ?? null) && filled($config['client_secret'] ?? null) && filled($config['webhook_id'] ?? null),
            'paystack' => filled($config['secret_key'] ?? null),
            default => false,
        };
    }
}

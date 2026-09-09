<?php

namespace Tests\Feature\Payments;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentGatewayTest extends TestCase
{
    use RefreshDatabase;

    public function test_paystack_webhook_is_verified_and_processed_once(): void
    {
        config()->set('payments.gateways.paystack.enabled', true);
        config()->set('payments.gateways.paystack.secret_key', 'test-secret');

        $user = User::factory()->create();
        $payment = Payment::create([
            'user_id' => $user->id,
            'provider' => 'paystack',
            'reference' => 'sub_test_123',
            'type' => 'subscription',
            'status' => 'pending',
            'currency' => 'NGN',
            'amount_minor' => 800000,
            'metadata' => ['type' => 'subscription', 'plan' => 'Basic', 'user_id' => $user->id],
        ]);
        $payload = json_encode([
            'event' => 'charge.success',
            'data' => [
                'reference' => $payment->reference,
                'status' => 'success',
                'amount' => $payment->amount_minor,
                'metadata' => $payment->metadata,
            ],
        ], JSON_THROW_ON_ERROR);
        $signature = hash_hmac('sha512', $payload, 'test-secret');

        $response = $this->call('POST', '/api/v1/payments/webhooks/paystack', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_PAYSTACK_SIGNATURE' => $signature,
        ], $payload);
        $response->assertOk()->assertJson(['received' => true, 'duplicate' => false]);
        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'paid']);
        $this->assertDatabaseHas('payment_events', ['provider' => 'paystack', 'event_id' => $payment->reference, 'status' => 'processed']);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'subscription_plan' => 'Basic', 'subscription_status' => 'active']);

        $duplicate = $this->call('POST', '/api/v1/payments/webhooks/paystack', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_PAYSTACK_SIGNATURE' => $signature,
        ], $payload);
        $duplicate->assertOk()->assertJson(['received' => true, 'duplicate' => true]);
        $this->assertDatabaseCount('payment_events', 1);
    }

    public function test_admin_can_toggle_gateway_mode_without_credentials(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
        ]);
        $this->actingAs($admin)->putJson('/api/v1/admin/payment-gateways/stripe', [
            'enabled' => true,
            'mode' => 'live',
            'default_currency' => 'USD',
        ])->assertOk()->assertJsonPath('settings.enabled', true)->assertJsonPath('settings.mode', 'live');

        $this->assertDatabaseHas('payment_gateway_settings', [
            'gateway' => 'stripe',
            'enabled' => true,
            'mode' => 'live',
            'default_currency' => 'USD',
        ]);
    }
}

<?php

namespace App\Services\Payments;

use Illuminate\Http\Request;

interface PaymentGateway
{
    public function name(): string;

    public function enabled(): bool;

    public function mode(): string;

    /** @return array<string,mixed> */
    public function initialize(array $payment, string $idempotencyKey): array;

    /** @return array<string,mixed> */
    public function verify(string $providerReference): array;

    public function verifyWebhook(Request $request): bool;

    /** @return array{event_id:string,event_type:string,payload:array<string,mixed>} */
    public function normalizeWebhook(Request $request): array;
}

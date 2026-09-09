<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentGatewaySetting;
use App\Services\Payments\PaymentGatewayManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class PaymentGatewaySettingsController extends Controller
{
    public function page()
    {
        return Inertia::render('Admin/PaymentGateways');
    }

    public function index(PaymentGatewayManager $manager): JsonResponse
    {
        return response()->json(['gateways' => $manager->settings()]);
    }

    public function update(Request $request, string $gateway, PaymentGatewayManager $manager): JsonResponse
    {
        abort_unless(array_key_exists($gateway, config('payments.gateways', [])), 404);
        $data = $request->validate([
            'enabled' => ['required', 'boolean'],
            'mode' => ['required', Rule::in(['sandbox', 'live'])],
            'default_currency' => ['nullable', 'string', 'size:3', 'regex:/^[A-Za-z]{3}$/'],
        ]);
        $setting = PaymentGatewaySetting::updateOrCreate(['gateway' => $gateway], [
            'enabled' => $data['enabled'],
            'mode' => strtolower($data['mode']),
            'default_currency' => isset($data['default_currency']) ? strtoupper($data['default_currency']) : null,
        ]);
        return response()->json(['gateway' => $gateway, 'settings' => $manager->settings()[$gateway], 'updated_at' => $setting->updated_at]);
    }
}

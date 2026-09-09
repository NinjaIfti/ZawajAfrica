<?php

use App\Http\Controllers\Admin\PaymentGatewaySettingsController;
use App\Http\Controllers\Api\V1\CoreController;
use App\Http\Controllers\Api\V1\PaymentController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::middleware('auth')->group(function () {
        Route::get('/me', [CoreController::class, 'me'])->name('me');
        Route::get('/subscription', [CoreController::class, 'subscription'])->name('subscription');
        Route::post('/payments/subscriptions/initialize', [PaymentController::class, 'initializeSubscription'])->name('payments.subscriptions.initialize');
        Route::get('/payments/{payment}/verify', [PaymentController::class, 'verify'])->name('payments.verify');
    });

    Route::post('/payments/webhooks/{gateway}', [PaymentController::class, 'webhook'])
        ->where('gateway', 'stripe|paypal|paystack')
        ->name('payments.webhooks');

    Route::middleware(['auth', 'admin'])->prefix('admin/payment-gateways')->name('admin.payment-gateways.')->group(function () {
        Route::get('/', [PaymentGatewaySettingsController::class, 'index'])->name('index');
        Route::put('/{gateway}', [PaymentGatewaySettingsController::class, 'update'])
            ->where('gateway', 'stripe|paypal|paystack')
            ->name('update');
    });
});

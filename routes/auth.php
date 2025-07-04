<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');

    // OTP Routes
    Route::post('otp/send', [App\Http\Controllers\Auth\OtpController::class, 'sendOTP'])
        ->name('otp.send');
    
    Route::post('otp/verify', [App\Http\Controllers\Auth\OtpController::class, 'verifyOTP'])
        ->name('otp.verify');
    
    Route::post('otp/resend', [App\Http\Controllers\Auth\OtpController::class, 'resendOTP'])
        ->name('otp.resend');
    
    Route::get('otp/status', [App\Http\Controllers\Auth\OtpController::class, 'checkStatus'])
        ->name('otp.status');
});

// Social Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('auth/google', [SocialAuthController::class, 'redirectToGoogle'])
        ->name('auth.google');
    
    Route::get('auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback'])
        ->name('auth.google.callback');
    
    Route::get('auth/apple', [SocialAuthController::class, 'redirectToApple'])
        ->name('auth.apple');
    
    Route::get('auth/apple/callback', [SocialAuthController::class, 'handleAppleCallback'])
        ->name('auth.apple.callback');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});

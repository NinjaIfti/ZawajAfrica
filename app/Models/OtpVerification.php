<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class OtpVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'otp_code',
        'purpose',
        'expires_at',
        'is_used',
        'used_at',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
        'is_used' => 'boolean'
    ];

    /**
     * Generate a new OTP for the given email and purpose
     */
    public static function generateOTP(string $email, string $purpose, int $validityMinutes = 10): string
    {
        // Invalidate any existing OTPs for this email and purpose
        self::where('email', $email)
            ->where('purpose', $purpose)
            ->where('is_used', false)
            ->update(['is_used' => true, 'used_at' => now()]);

        // Generate 6-digit OTP
        $otp = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        // Store OTP in database
        self::create([
            'email' => $email,
            'otp_code' => $otp,
            'purpose' => $purpose,
            'expires_at' => Carbon::now()->addMinutes($validityMinutes),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);

        return $otp;
    }

    /**
     * Verify OTP
     */
    public static function verifyOTP(string $email, string $otp, string $purpose): bool
    {
        $otpRecord = self::where('email', $email)
            ->where('otp_code', $otp)
            ->where('purpose', $purpose)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->first();

        if ($otpRecord) {
            $otpRecord->update([
                'is_used' => true,
                'used_at' => now()
            ]);
            return true;
        }

        return false;
    }

    /**
     * Check if OTP exists and is valid
     */
    public static function isValidOTP(string $email, string $otp, string $purpose): bool
    {
        return self::where('email', $email)
            ->where('otp_code', $otp)
            ->where('purpose', $purpose)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->exists();
    }

    /**
     * Clean up expired OTPs
     */
    public static function cleanupExpired(): int
    {
        return self::where('expires_at', '<', now())->delete();
    }

    /**
     * Check rate limiting for OTP generation
     */
    public static function canGenerateOTP(string $email, string $purpose, int $maxAttemptsPerHour = 5): bool
    {
        $cacheKey = "otp_rate_limit:{$email}:{$purpose}";
        $attempts = Cache::get($cacheKey, 0);
        
        if ($attempts >= $maxAttemptsPerHour) {
            return false;
        }

        Cache::put($cacheKey, $attempts + 1, 3600); // 1 hour
        return true;
    }

    /**
     * Get remaining time for OTP
     */
    public function getRemainingTimeAttribute(): int
    {
        if ($this->expires_at->isPast()) {
            return 0;
        }
        
        return $this->expires_at->diffInSeconds(now());
    }

    /**
     * Check if OTP is expired
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if OTP is valid (not used and not expired)
     */
    public function getIsValidAttribute(): bool
    {
        return !$this->is_used && !$this->is_expired;
    }
}

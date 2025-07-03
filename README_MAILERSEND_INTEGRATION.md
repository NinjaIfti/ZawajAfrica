# MailerSend Integration for ZawajAfrica

## Overview

This document explains the MailerSend integration for ZawajAfrica's email system, specifically for OTP verification and password reset emails.

## Features Integrated

✅ **OTP Verification Emails**
- Triggered on signup, login, or email verification
- Format: `ZawajAfrica OTP: 284019. This code expires in 10 minutes.`
- Beautiful HTML email template with Islamic greeting
- Rate limiting and security features

✅ **Password Reset Emails**
- Subject: `ZawajAfrica – Reset Your Password`
- Professional email template with reset button
- Security notices and instructions
- Uses MailerSend for reliable delivery

## Environment Variables Required

Add these variables to your `.env` file:

```env
# MailerSend Configuration
MAILERSEND_ENABLED=true
MAILERSEND_API_KEY=your_mailersend_api_key_here
MAILERSEND_API_URL=https://api.mailersend.com/v1
MAILERSEND_FROM_EMAIL=admin@zawajafrica.com.ng
MAILERSEND_FROM_NAME="ZawajAfrica"
```

### How to Get Your MailerSend API Key

1. Log in to your MailerSend dashboard
2. Go to **Settings** → **API Tokens**
3. Click **Generate new token**
4. Copy the generated API key
5. Add it to your `.env` file as `MAILERSEND_API_KEY`

## Files Added/Modified

### New Files Created:
- `app/Services/MailerSendService.php` - Main MailerSend service
- `app/Models/OtpVerification.php` - OTP verification model
- `app/Http/Controllers/Auth/OtpController.php` - OTP handling controller
- `app/Console/Commands/CleanupExpiredOtps.php` - Cleanup expired OTPs
- `database/migrations/2025_07_03_093405_create_otp_verifications_table.php` - OTP database table

### Files Modified:
- `config/services.php` - Added MailerSend configuration
- `app/Http/Controllers/Auth/PasswordResetLinkController.php` - Updated to use MailerSend
- `routes/auth.php` - Added OTP verification routes

## API Endpoints

### OTP Endpoints

**Send OTP**
```http
POST /otp/send
Content-Type: application/json

{
    "email": "user@example.com",
    "purpose": "login" // login, signup, password_reset, email_verification
}
```

**Verify OTP**
```http
POST /otp/verify
Content-Type: application/json

{
    "email": "user@example.com",
    "otp": "123456",
    "purpose": "login"
}
```

**Resend OTP**
```http
POST /otp/resend
Content-Type: application/json

{
    "email": "user@example.com",
    "purpose": "login"
}
```

**Check OTP Status**
```http
GET /otp/status?email=user@example.com&purpose=login
```

## Email Templates

### OTP Email Features:
- **Islamic Greeting**: "Salam Alaikum" with user's name
- **Prominent OTP Display**: Large, bold 6-digit code
- **Security Warnings**: Clear instructions about not sharing OTP
- **Expiration Notice**: 10-minute expiration clearly stated
- **Professional Design**: ZawajAfrica branding with gradient header
- **Contact Information**: Support email for help

### Password Reset Email Features:
- **Islamic Greeting**: "Salam Alaikum" with user's name
- **Clear CTA Button**: Prominent "Reset My Password" button
- **Security Information**: Link expiration and safety notes
- **Fallback URL**: Plain text link if button doesn't work
- **Professional Design**: Consistent with ZawajAfrica branding

## Security Features

### Rate Limiting:
- **OTP Generation**: Max 5 OTPs per email per hour
- **API Rate Limiting**: 3 send attempts per 5 minutes
- **Verification Attempts**: 5 verification attempts per 5 minutes
- **Resend Limiting**: 2 resend attempts per 10 minutes

### OTP Security:
- **6-digit codes**: Cryptographically secure random generation
- **10-minute expiration**: Automatic expiration for security
- **Single-use**: OTPs are invalidated after use
- **IP tracking**: Request IP addresses are logged
- **User agent tracking**: Browser information is logged

### Database Cleanup:
- **Expired OTPs**: Automatically cleaned up
- **Old records**: Used OTPs older than 7 days are removed
- **Command**: `php artisan otp:cleanup`

## Usage Examples

### Frontend Integration:

```javascript
// Send OTP
const response = await fetch('/otp/send', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
        email: 'user@example.com',
        purpose: 'login'
    })
});

// Verify OTP
const verifyResponse = await fetch('/otp/verify', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
        email: 'user@example.com',
        otp: '123456',
        purpose: 'login'
    })
});
```

### Backend Integration:

```php
use App\Services\MailerSendService;
use App\Models\OtpVerification;

// Send OTP
$mailerSend = app(MailerSendService::class);
$otp = OtpVerification::generateOTP('user@example.com', 'login');
$result = $mailerSend->sendOTP('user@example.com', $otp, 'John Doe');

// Verify OTP
$isValid = OtpVerification::verifyOTP('user@example.com', '123456', 'login');
```

## Maintenance Commands

### Clean up expired OTPs:
```bash
# With confirmation
php artisan otp:cleanup

# Force cleanup without confirmation
php artisan otp:cleanup --force
```

### Test MailerSend connection:
```php
use App\Services\MailerSendService;

$mailerSend = app(MailerSendService::class);
$status = $mailerSend->testConnection();
```

## Monitoring and Logs

All email operations are logged with the following information:
- **Send attempts**: Success/failure with message IDs
- **OTP generation**: Email, purpose, expiration time
- **Verification attempts**: Valid/invalid attempts with details
- **Rate limiting**: Blocked attempts due to limits
- **Errors**: Detailed error messages for debugging

### Log Locations:
- **Laravel Logs**: `storage/logs/laravel.log`
- **Email Activity**: Tagged with 'MailerSend' for easy filtering

## Troubleshooting

### Common Issues:

1. **"MailerSend not properly configured"**
   - Check that `MAILERSEND_ENABLED=true`
   - Verify `MAILERSEND_API_KEY` is set correctly
   - Ensure `MAILERSEND_FROM_EMAIL` is verified in MailerSend

2. **"Too many OTP requests"**
   - Rate limiting is active - wait before trying again
   - Check if rate limits need adjustment in code

3. **"Invalid or expired OTP"**
   - OTP may have expired (10-minute limit)
   - OTP may have been used already
   - Verify the exact 6-digit code

4. **Emails not being delivered**
   - Check MailerSend dashboard for delivery status
   - Verify domain authentication (SPF, DKIM, DMARC)
   - Check spam folders

### Testing:

```bash
# Test OTP functionality
php artisan tinker
> use App\Services\MailerSendService;
> $service = app(MailerSendService::class);
> $service->sendOTP('test@example.com', '123456', 'Test User');

# Test password reset
> $service->sendPasswordReset('test@example.com', 'https://example.com/reset', 'Test User');
```

## Performance Considerations

- **Database Indexing**: Optimized indexes for fast OTP lookups
- **Rate Limiting**: Prevents abuse and reduces API costs
- **Cleanup Commands**: Regular cleanup prevents database bloat
- **Caching**: Rate limit data is cached for performance

## Integration with Existing System

The MailerSend integration coexists with your existing Zoho Mail system:
- **MailerSend**: Used for OTP and password reset emails
- **Zoho Mail**: Continues to handle other email types (notifications, welcome emails, etc.)
- **No conflicts**: Both systems can operate simultaneously

## Next Steps

1. **Add API key to environment variables**
2. **Test OTP functionality in development**
3. **Configure domain authentication in MailerSend**
4. **Set up monitoring and alerting**
5. **Schedule regular OTP cleanup**

---

For support or questions about this integration, contact the development team. 
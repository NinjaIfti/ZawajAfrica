# Email Configuration Guide - Multiple Sender Addresses

This guide explains how to configure multiple email addresses for different types of emails in ZawajAfrica.

## Email Address Usage

### Current Setup
- **Therapist emails**: `support@zawajafrica.com.ng` (bookings, confirmations, reminders, cancellations)
- **Admin emails**: `admin@zawajafrica.com.ng` (weekly reports, admin notifications)
- **Support emails**: `support@zawajafrica.com.ng` (welcome emails, match suggestions)
- **No-reply emails**: `noreply@zawajafrica.com.ng` (system notifications)

## Environment Variables Required

Add these variables to your `.env` file:

```env
# Main Zoho Mail Configuration (required)
ZOHO_MAIL_ENABLED=true
ZOHO_MAIL_HOST=smtp.zoho.com
ZOHO_MAIL_PORT=587
ZOHO_MAIL_USERNAME=your-main-email@zawajafrica.com.ng
ZOHO_MAIL_PASSWORD=your-app-password
ZOHO_MAIL_ENCRYPTION=tls
ZOHO_MAIL_FROM_ADDRESS=noreply@zawajafrica.com.ng
ZOHO_MAIL_FROM_NAME="ZawajAfrica"

# Specific Sender Addresses
ZOHO_MAIL_SUPPORT_ADDRESS=support@zawajafrica.com.ng
ZOHO_MAIL_SUPPORT_NAME="ZawajAfrica Support Team"

ZOHO_MAIL_ADMIN_ADDRESS=admin@zawajafrica.com.ng
ZOHO_MAIL_ADMIN_NAME="ZawajAfrica Admin"

ZOHO_MAIL_THERAPIST_ADDRESS=support@zawajafrica.com.ng
ZOHO_MAIL_THERAPIST_NAME="ZawajAfrica Therapy Services"

ZOHO_MAIL_NOREPLY_ADDRESS=noreply@zawajafrica.com.ng
ZOHO_MAIL_NOREPLY_NAME="ZawajAfrica"
```

## Email Types and Senders

### Therapist-Related Emails (support@zawajafrica.com.ng)
- Booking confirmations
- Payment confirmations
- Session reminders
- Booking cancellations
- Therapist availability updates

### Admin Emails (admin@zawajafrica.com.ng)
- Weekly AI reports
- Admin notifications
- System alerts
- Broadcast emails to users

### User Support (support@zawajafrica.com.ng)
- Welcome emails for new users
- Match suggestions
- General user support

### System Notifications (noreply@zawajafrica.com.ng)
- Password resets
- Email verification
- Automated system messages

## Setup Process

### 1. Zoho Mail Account Setup
You need to set up email addresses in your Zoho Mail domain:
- `support@zawajafrica.com.ng`
- `admin@zawajafrica.com.ng` 
- `noreply@zawajafrica.com.ng`

### 2. App Passwords
For each email address, generate app-specific passwords in Zoho Mail:
1. Go to Zoho Mail Settings
2. Navigate to Security
3. Generate App Password for SMTP
4. Use these app passwords in your ENV configuration

### 3. Domain Authentication
Ensure your domain is properly authenticated with:
- SPF records
- DKIM signing
- DMARC policy

## Single vs Multiple SMTP Accounts

### Option 1: Single SMTP Account (Recommended)
- Use one main SMTP account (e.g., `system@zawajafrica.com.ng`)
- Set different "from" addresses for different email types
- Simpler to manage and configure

### Option 2: Multiple SMTP Accounts
- Separate SMTP credentials for each email address
- More complex configuration
- Better separation of concerns

## Testing

Test your email configuration:

```bash
# Test therapist email
php artisan tinker
> app(App\Services\ZohoMailService::class)->sendTherapistEmail(new \Illuminate\Mail\Mailable(), 'test@example.com');

# Test admin email  
> app(App\Services\ZohoMailService::class)->sendAdminEmail(new \Illuminate\Mail\Mailable(), 'admin@zawajafrica.com.ng');
```

## Troubleshooting

### Common Issues
1. **Authentication Failed**: Check app passwords and SMTP credentials
2. **From Address Rejected**: Ensure domain is verified in Zoho
3. **Rate Limiting**: Zoho has sending limits per account
4. **SPF/DKIM Issues**: Verify domain authentication records

### Logs
Check Laravel logs for email sending errors:
```bash
tail -f storage/logs/laravel.log | grep -i mail
```

## Current Implementation

The system automatically routes emails based on type:
- Therapist notifications → `support@zawajafrica.com.ng`
- Admin reports → `admin@zawajafrica.com.ng`
- User communications → `support@zawajafrica.com.ng`
- System emails → `noreply@zawajafrica.com.ng`

No code changes required - just update your ENV configuration. 
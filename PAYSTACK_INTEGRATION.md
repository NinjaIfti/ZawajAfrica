# Paystack Integration for ZawajAfrica

## 🚀 Setup Instructions

### 1. Environment Configuration

Add the following to your `.env` file:

```env
# Paystack Configuration
PAYSTACK_PUBLIC_KEY=pk_test_d115d564043ab427d36734a48ba364b486cc3f50
PAYSTACK_SECRET_KEY=sk_test_2e0475ff653d15997ae8fa2c8ac74c9a44308760
PAYSTACK_PAYMENT_URL=https://api.paystack.co
```

### 2. Paystack Dashboard Settings

For localhost development, add these URLs to your Paystack Dashboard:

**Test Callback URL:**
```
http://localhost:8000/payment/callback
```

**Test Webhook URL:**
```
http://localhost:8000/paystack/webhook
```

### 3. Running the Application

1. **Start the Laravel development server:**
   ```bash
   php artisan serve
   ```

2. **Start the frontend development server:**
   ```bash
   npm run dev
   ```

3. **Access the application:**
   - Visit: `http://localhost:8000`
   - Login to test the payment features

## 💳 Payment Features

### Subscription Payments
- Navigate to `/subscription` to view subscription plans
- Select a plan and agree to terms
- Click the plan button to initiate payment
- You'll be redirected to Paystack for secure payment
- After successful payment, you'll be redirected back to the app

### Therapist Booking Payments
- Navigate to `/therapists` to view available therapists
- Click on a therapist to view their profile
- Select date, time, and platform
- Click "Confirm Booking" to proceed to payment options
- Select "Paystack" payment option
- Complete payment through Paystack
- Booking will be confirmed after successful payment

## 🔧 Technical Implementation

### Payment Flow
1. **Frontend** initiates payment via AJAX call
2. **Backend** creates payment record and calls Paystack API
3. **Paystack** returns authorization URL
4. **Frontend** redirects user to Paystack
5. **User** completes payment on Paystack
6. **Paystack** redirects back to callback URL
7. **Backend** verifies payment and updates records
8. **User** sees success/failure message

### Database Changes
- Added subscription fields to `users` table:
  - `subscription_plan`
  - `subscription_status`
  - `subscription_expires_at`
  
- Added payment fields to `therapist_bookings` table:
  - `booking_date`
  - `booking_time`
  - `notes`
  - `amount`
  - `payment_reference`
  - `payment_status`

### New Routes
- `POST /payment/subscription/initialize` - Initialize subscription payment
- `POST /payment/therapist/initialize` - Initialize therapist booking payment
- `GET /payment/callback` - Handle payment callback
- `POST /paystack/webhook` - Handle webhook notifications

### Key Files
- `app/Services/PaystackService.php` - Paystack API integration
- `app/Http/Controllers/PaymentController.php` - Payment processing logic
- `resources/js/Pages/Subscription/Index.vue` - Updated subscription page
- `resources/js/Pages/Therapists/Show.vue` - Updated therapist booking page

## 🛡️ Security Features

- Webhook signature verification
- CSRF protection on all forms
- Payment reference validation
- Database transactions for data integrity

## 📝 Testing

### Test Cards (Paystack)
Use these test card numbers in the Paystack checkout:

**Successful Payment:**
- Card: `4084084084084081`
- CVV: `408`
- Expiry: Any future date
- PIN: `0000`

**Failed Payment:**
- Card: `4084084084084061`
- CVV: Any 3 digits
- Expiry: Any future date

### Test Process
1. Create a user account
2. Navigate to subscription page
3. Select a plan and initiate payment
4. Use test card details
5. Verify payment completion and subscription activation

## 🚨 Important Notes

- **Test Environment**: Currently configured for Paystack test environment
- **Production**: Update keys and URLs for production deployment
- **Webhooks**: Ensure webhook URL is accessible from Paystack servers
- **SSL**: Production webhooks require HTTPS URLs

## 🔄 Production Deployment

When moving to production:

1. Update `.env` with live Paystack keys:
   ```env
   PAYSTACK_PUBLIC_KEY=pk_live_your_live_public_key
   PAYSTACK_SECRET_KEY=sk_live_your_live_secret_key
   ```

2. Update Paystack Dashboard URLs:
   ```
   Callback URL: https://yourdomain.com/payment/callback
   Webhook URL: https://yourdomain.com/paystack/webhook
   ```

3. Test thoroughly with live (small amount) transactions

## 🆘 Troubleshooting

**Payment fails to initialize:**
- Check Paystack keys in `.env`
- Verify internet connection
- Check Laravel logs for errors

**Callback not working:**
- Ensure callback URL is accessible
- Check route definition
- Verify CSRF middleware exclusion for webhooks

**Webhook issues:**
- Verify webhook URL in Paystack dashboard
- Check signature verification logic
- Monitor Laravel logs

## 📞 Support

For technical issues:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Check browser console for JavaScript errors
3. Verify Paystack dashboard for payment status
4. Contact Paystack support for API issues 
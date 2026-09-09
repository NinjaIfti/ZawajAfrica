# Payment provider implementation notes

This project will use a provider-neutral payment layer with server-side verification, durable local idempotency, and webhook-first fulfillment. No live credentials are included.

## Stripe

Official sources: [authentication](https://docs.stripe.com/api/authentication), [Checkout Sessions](https://docs.stripe.com/api/checkout/sessions/create), [fulfillment](https://docs.stripe.com/checkout/fulfillment.md?payment-ui=stripe-hosted), [webhook signatures](https://docs.stripe.com/webhooks/signature), [idempotency](https://docs.stripe.com/api/idempotent_requests), and [testing](https://docs.stripe.com/testing).

Stripe POST mutations require an idempotency key. Webhooks must be verified against the raw request body and endpoint-specific signing secret. Event delivery can be duplicated or out of order, so local event IDs and monotonic state transitions are required. Browser redirects are not payment proof.

## PayPal

Official sources: [REST authentication](https://developer.paypal.com/api/rest/authentication), [Orders](https://developer.paypal.com/api/orders/v2), [idempotency](https://developer.paypal.com/api/rest/reference/idempotency), [webhooks](https://developer.paypal.com/api/rest/webhooks), [webhook verification](https://developer.paypal.com/api/webhooks/v1/verify-webhook-signature-post), [currency codes](https://developer.paypal.com/reference/currency-codes), and [sandbox testing](https://developer.paypal.com/sandbox-testing/overview).

PayPal uses OAuth client credentials and `PayPal-Request-Id` for mutation idempotency. Webhooks require raw-body signature verification or PayPal's verification endpoint. Sandbox and live base URLs and webhook IDs must remain separate.

## Paystack

Official sources: [authentication](https://paystack.com/docs/api/authentication/), [transactions](https://paystack.com/docs/api/transaction/), [verification](https://paystack.com/docs/payments/verify-payments/), [webhooks](https://paystack.com/docs/payments/webhooks/), [refunds](https://paystack.com/docs/api/refund/), and [testing](https://paystack.com/docs/payments/test-payments/).

Paystack uses server-side secret-key calls and transaction verification. Webhooks are verified with HMAC-SHA512 over the exact raw body and the secret key. Paystack does not document a universal idempotency header, so local unique payment references and durable event deduplication are mandatory.

<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MonnifyService
{
    private $baseUrl;
    private $apiKey;
    private $secretKey;
    private $contractCode;

    public function __construct()
    {
        $this->baseUrl = config('services.monnify.base_url');
        $this->apiKey = config('services.monnify.api_key');
        $this->secretKey = config('services.monnify.secret_key');
        $this->contractCode = config('services.monnify.contract_code');
    }

    /**
     * Get access token for API requests
     */
    private function getAccessToken()
    {
        $credentials = base64_encode($this->apiKey . ':' . $this->secretKey);
        
        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . $credentials,
            'Content-Type' => 'application/json'
        ])->post($this->baseUrl . '/auth/login');

        if ($response->successful()) {
            return $response->json()['responseBody']['accessToken'];
        }

        throw new \Exception('Failed to get Monnify access token: ' . $response->body());
    }

    /**
     * Initialize payment transaction
     */
    public function initializePayment(array $paymentData)
    {
        try {
            $accessToken = $this->getAccessToken();
            
            $payload = [
                'amount' => $paymentData['amount'],
                'customerName' => $paymentData['customer_name'],
                'customerEmail' => $paymentData['email'],
                'paymentReference' => $paymentData['reference'],
                'paymentDescription' => $paymentData['description'] ?? 'Payment for services',
                'redirectUrl' => $paymentData['callback_url'],
                'paymentMethods' => ['CARD', 'ACCOUNT_TRANSFER'],
                'currencyCode' => 'NGN',
                'contractCode' => $this->contractCode,
                'metadata' => $paymentData['metadata'] ?? []
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl . '/merchant/transactions/init-transaction', $payload);

            Log::info('Monnify Initialize Payment Response', [
                'status_code' => $response->status(),
                'response' => $response->json()
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if ($data['requestSuccessful']) {
                    return [
                        'status' => true,
                        'data' => [
                            'authorization_url' => $data['responseBody']['checkoutUrl'],
                            'access_code' => $data['responseBody']['transactionReference'],
                            'reference' => $data['responseBody']['paymentReference']
                        ]
                    ];
                }
            }

            return [
                'status' => false,
                'message' => $response->json()['responseMessage'] ?? 'Payment initialization failed'
            ];

        } catch (\Exception $e) {
            Log::error('Monnify Payment Initialization Error', [
                'error' => $e->getMessage(),
                'payment_data' => $paymentData
            ]);

            return [
                'status' => false,
                'message' => 'Payment service temporarily unavailable'
            ];
        }
    }

    /**
     * Verify payment transaction
     */
    public function verifyPayment(string $reference)
    {
        try {
            $accessToken = $this->getAccessToken();
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json'
            ])->get($this->baseUrl . '/merchant/transactions/query', [
                'paymentReference' => $reference
            ]);

            Log::info('Monnify Verify Payment Response', [
                'reference' => $reference,
                'status_code' => $response->status(),
                'response' => $response->json()
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if ($data['requestSuccessful']) {
                    $transaction = $data['responseBody'];
                    
                    return [
                        'status' => true,
                        'data' => [
                            'status' => strtolower($transaction['paymentStatus']) === 'paid' ? 'success' : 'failed',
                            'reference' => $transaction['paymentReference'],
                            'amount' => $transaction['amountPaid'] * 100, // Convert to kobo for consistency
                            'currency' => 'NGN',
                            'paid_at' => $transaction['paidOn'] ?? now(),
                            'metadata' => $transaction['metaData'] ?? []
                        ]
                    ];
                }
            }

            return [
                'status' => false,
                'message' => $response->json()['responseMessage'] ?? 'Payment verification failed'
            ];

        } catch (\Exception $e) {
            Log::error('Monnify Payment Verification Error', [
                'error' => $e->getMessage(),
                'reference' => $reference
            ]);

            return [
                'status' => false,
                'message' => 'Payment verification service temporarily unavailable'
            ];
        }
    }

    /**
     * Check if Monnify service is properly configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey) && 
               !empty($this->secretKey) && 
               !empty($this->contractCode) && 
               !empty($this->baseUrl);
    }

    /**
     * Get supported payment methods
     */
    public function getPaymentMethods(): array
    {
        return [
            'card' => [
                'name' => 'Debit/Credit Card',
                'description' => 'Pay with Visa, Mastercard, Verve cards',
                'icon' => 'credit-card'
            ],
            'transfer' => [
                'name' => 'Bank Transfer',
                'description' => 'Direct bank account transfer',
                'icon' => 'bank'
            ],
            'ussd' => [
                'name' => 'USSD',
                'description' => 'Pay with USSD code',
                'icon' => 'phone'
            ]
        ];
    }
} 
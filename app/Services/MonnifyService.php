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
        
        // Monnify auth endpoint structure
        $authUrl = rtrim($this->baseUrl, '/') . '/api/v1/auth/login';
        
        $response = Http::timeout(30)->withHeaders([
            'Authorization' => 'Basic ' . $credentials,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ])->post($authUrl);

        if ($response->successful()) {
            $data = $response->json();
            if (isset($data['responseBody']['accessToken'])) {
                return $data['responseBody']['accessToken'];
            }
            throw new \Exception('No access token in response');
        }

        throw new \Exception('Failed to get Monnify access token: ' . $response->status() . ' - ' . $response->body());
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
                'customerName' => $paymentData['customerName'],
                'customerEmail' => $paymentData['customerEmail'],
                'paymentReference' => $paymentData['paymentReference'],
                'paymentDescription' => $paymentData['paymentDescription'] ?? 'Payment for services',
                'redirectUrl' => $paymentData['redirectUrl'],
                'paymentMethods' => ['CARD', 'ACCOUNT_TRANSFER', 'USSD'],
                'currencyCode' => 'NGN',
                'contractCode' => $this->contractCode,
                'metadata' => $paymentData['metadata'] ?? []
            ];

            $initUrl = rtrim($this->baseUrl, '/') . '/api/v1/merchant/transactions/init-transaction';
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json'
            ])->post($initUrl, $payload);

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
            
            $queryUrl = rtrim($this->baseUrl, '/') . '/api/v1/merchant/transactions/query';
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json'
            ])->get($queryUrl, [
                'paymentReference' => $reference
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
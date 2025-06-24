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
                    // Return format similar to Paystack
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
            Log::info('Monnify: Starting payment verification', [
                'reference' => $reference
            ]);

            $accessToken = $this->getAccessToken();
            
            $queryUrl = rtrim($this->baseUrl, '/') . '/api/v1/merchant/transactions/query';
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json'
            ])->get($queryUrl, [
                'paymentReference' => $reference
            ]);

            Log::info('Monnify: Verification API response', [
                'reference' => $reference,
                'status_code' => $response->status(),
                'response_body' => $response->json()
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if ($data['requestSuccessful']) {
                    $transaction = $data['responseBody'];
                    
                    Log::info('Monnify: Transaction details', [
                        'reference' => $reference,
                        'transaction' => $transaction
                    ]);
                    
                    // Handle metadata properly - try multiple possible field names
                    $metadata = [];
                    
                    // Check for various metadata field names that Monnify might use
                    $metadataFields = ['metaData', 'customerMetaData', 'metadata', 'transactionMetaData'];
                    
                    foreach ($metadataFields as $field) {
                        if (isset($transaction[$field])) {
                            Log::info('Monnify: Found metadata in field', [
                                'reference' => $reference,
                                'field' => $field,
                                'raw_metadata' => $transaction[$field]
                            ]);
                            
                            if (is_string($transaction[$field])) {
                                $decoded = json_decode($transaction[$field], true);
                                if (json_last_error() === JSON_ERROR_NONE) {
                                    $metadata = $decoded;
                                } else {
                                    Log::warning('Monnify: Failed to decode metadata JSON', [
                                        'reference' => $reference,
                                        'field' => $field,
                                        'raw_data' => $transaction[$field],
                                        'json_error' => json_last_error_msg()
                                    ]);
                                }
                            } elseif (is_array($transaction[$field])) {
                                $metadata = $transaction[$field];
                            }
                            break; // Use the first one found
                        }
                    }
                    
                    // If no metadata found in transaction, try to get it from our local database
                    if (empty($metadata)) {
                        Log::warning('Monnify: No metadata found in transaction response, checking local database', [
                            'reference' => $reference
                        ]);
                        
                        // Try to find the booking by payment reference
                        $booking = \App\Models\TherapistBooking::where('payment_reference', $reference)->first();
                        if ($booking) {
                            $metadata = [
                                'type' => 'therapist_booking',
                                'booking_id' => $booking->id,
                                'therapist_id' => $booking->therapist_id,
                                'user_id' => $booking->user_id,
                                'gateway' => 'monnify'
                            ];
                            
                            Log::info('Monnify: Reconstructed metadata from local booking', [
                                'reference' => $reference,
                                'booking_id' => $booking->id,
                                'metadata' => $metadata
                            ]);
                        } else {
                            Log::error('Monnify: No local booking found for reference', [
                                'reference' => $reference
                            ]);
                        }
                    }
                    
                    $paymentStatus = strtolower($transaction['paymentStatus']) === 'paid' ? 'success' : 'failed';
                    
                    Log::info('Monnify: Final verification result', [
                        'reference' => $reference,
                        'payment_status' => $paymentStatus,
                        'amount' => $transaction['amountPaid'],
                        'metadata' => $metadata
                    ]);
                    
                    // Return in Paystack-compatible format
                    return [
                        'status' => true,
                        'data' => [
                            'status' => $paymentStatus,
                            'reference' => $transaction['paymentReference'],
                            'amount' => $transaction['amountPaid'] * 100, // Convert to kobo for consistency
                            'currency' => 'NGN',
                            'paid_at' => $transaction['paidOn'] ?? now(),
                            'metadata' => $metadata
                        ]
                    ];
                }
            }

            Log::error('Monnify: Payment verification failed', [
                'reference' => $reference,
                'response_status' => $response->status(),
                'response_body' => $response->json()
            ]);

            return [
                'status' => false,
                'message' => $response->json()['responseMessage'] ?? 'Payment verification failed'
            ];

        } catch (\Exception $e) {
            Log::error('Monnify: Payment verification exception', [
                'reference' => $reference,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
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

    /**
     * Create a reserved account for the user
     */
    public function createReservedAccount(array $customerData)
    {
        try {
            Log::info('Monnify: Creating reserved account', [
                'customer_email' => $customerData['customerEmail']
            ]);

            $accessToken = $this->getAccessToken();
            
            $payload = [
                'accountReference' => $customerData['accountReference'],
                'accountName' => $customerData['accountName'],
                'currencyCode' => 'NGN',
                'contractCode' => $this->contractCode,
                'customerEmail' => $customerData['customerEmail'],
                'customerName' => $customerData['customerName'],
                'getAllAvailableBanks' => true
            ];

            $createUrl = rtrim($this->baseUrl, '/') . '/api/v2/bank-transfer/reserved-accounts';
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json'
            ])->post($createUrl, $payload);

            Log::info('Monnify: Reserved account creation response', [
                'customer_email' => $customerData['customerEmail'],
                'status_code' => $response->status(),
                'response_body' => $response->json()
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if ($data['requestSuccessful']) {
                    return [
                        'status' => true,
                        'data' => $data['responseBody']
                    ];
                }
            }

            // Handle the case where account already exists
            $responseData = $response->json();
            if (isset($responseData['responseMessage']) && 
                strpos($responseData['responseMessage'], 'cannot reserve more than') !== false) {
                
                Log::info('Monnify: Account already exists, attempting to retrieve existing account', [
                    'customer_email' => $customerData['customerEmail'],
                    'account_reference' => $customerData['accountReference']
                ]);
                
                // Try to get the existing account details
                $existingAccount = $this->getReservedAccountDetails($customerData['accountReference']);
                if ($existingAccount['status']) {
                    Log::info('Monnify: Retrieved existing reserved account', [
                        'customer_email' => $customerData['customerEmail'],
                        'account_reference' => $customerData['accountReference']
                    ]);
                    
                    return [
                        'status' => true,
                        'data' => $existingAccount['data'],
                        'message' => 'Using existing reserved account'
                    ];
                }
                
                return [
                    'status' => false,
                    'message' => 'Account limit reached. Please use a different email or contact support.'
                ];
            }

            return [
                'status' => false,
                'message' => $responseData['responseMessage'] ?? 'Failed to create reserved account'
            ];

        } catch (\Exception $e) {
            Log::error('Monnify: Reserved account creation exception', [
                'customer_email' => $customerData['customerEmail'] ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'status' => false,
                'message' => 'Reserved account service temporarily unavailable'
            ];
        }
    }

    /**
     * Update customer KYC information for reserved account
     */
    public function updateKycInfo(string $accountReference, array $kycData)
    {
        try {
            $accessToken = $this->getAccessToken();
            
            Log::info('Monnify: Updating KYC info', [
                'account_reference' => $accountReference,
                'has_bvn' => isset($kycData['bvn']),
                'has_nin' => isset($kycData['nin'])
            ]);

            $endpoint = "/api/v1/bank-transfer/reserved-accounts/{$accountReference}/kyc-info";
            
            $response = Http::timeout(60) // 60 seconds timeout for KYC operations
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ])
                ->put($this->baseUrl . $endpoint, $kycData);

            $responseData = $response->json();

            Log::info('Monnify: KYC update response', [
                'account_reference' => $accountReference,
                'status_code' => $response->status(),
                'response_body' => $responseData
            ]);

            if ($response->successful() && isset($responseData['requestSuccessful']) && $responseData['requestSuccessful']) {
                return [
                    'status' => true,
                    'message' => 'KYC information updated successfully',
                    'data' => $responseData['responseBody'] ?? null
                ];
            }

            return [
                'status' => false,
                'message' => $responseData['responseMessage'] ?? 'Failed to update KYC information',
                'error' => $responseData
            ];

        } catch (\Exception $e) {
            Log::error('Monnify: KYC update failed', [
                'account_reference' => $accountReference,
                'error' => $e->getMessage()
            ]);

            return [
                'status' => false,
                'message' => 'KYC update failed: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get reserved account details including KYC status
     */
    public function getReservedAccountDetails(string $accountReference)
    {
        try {
            Log::info('Monnify: Getting reserved account details', [
                'account_reference' => $accountReference
            ]);

            $accessToken = $this->getAccessToken();
            
            $detailsUrl = rtrim($this->baseUrl, '/') . '/api/v2/bank-transfer/reserved-accounts/' . $accountReference;
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json'
            ])->get($detailsUrl);

            Log::info('Monnify: Reserved account details response', [
                'account_reference' => $accountReference,
                'status_code' => $response->status(),
                'response_body' => $response->json()
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if ($data['requestSuccessful']) {
                    return [
                        'status' => true,
                        'data' => $data['responseBody']
                    ];
                }
            }

            return [
                'status' => false,
                'message' => $response->json()['responseMessage'] ?? 'Failed to get account details'
            ];

        } catch (\Exception $e) {
            Log::error('Monnify: Get account details exception', [
                'account_reference' => $accountReference,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'status' => false,
                'message' => 'Account details service temporarily unavailable'
            ];
        }
    }

    /**
     * Generate a unique account reference for a user
     */
    public function generateAccountReference(int $userId): string
    {
        return 'zawaj_user_' . $userId;
    }

    /**
     * Validate BVN format (11 digits)
     */
    public function validateBvn(string $bvn): bool
    {
        return preg_match('/^\d{11}$/', $bvn);
    }

    /**
     * Validate NIN format (11 digits)
     */
    public function validateNin(string $nin): bool
    {
        return preg_match('/^\d{11}$/', $nin);
    }
} 
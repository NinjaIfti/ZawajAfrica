<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaystackService
{
    private $secretKey;
    private $publicKey;
    private $baseUrl;

    public function __construct()
    {
        $this->secretKey = config('services.paystack.secret_key');
        $this->publicKey = config('services.paystack.public_key');
        $this->baseUrl = config('services.paystack.payment_url', 'https://api.paystack.co');
    }

    /**
     * Initialize a payment transaction
     */
    public function initializePayment($data)
    {
        $url = $this->baseUrl . '/transaction/initialize';
        
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->secretKey,
            'Content-Type' => 'application/json',
        ])->post($url, $data);

        return $response->json();
    }

    /**
     * Verify a payment transaction
     */
    public function verifyPayment($reference)
    {
        $url = $this->baseUrl . '/transaction/verify/' . $reference;
        
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->secretKey,
        ])->get($url);

        return $response->json();
    }

    /**
     * Get all transactions
     */
    public function getTransactions()
    {
        $url = $this->baseUrl . '/transaction';
        
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->secretKey,
        ])->get($url);

        return $response->json();
    }

    /**
     * Get customer by email
     */
    public function getCustomer($email)
    {
        $url = $this->baseUrl . '/customer/' . $email;
        
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->secretKey,
        ])->get($url);

        return $response->json();
    }

    /**
     * Create a customer
     */
    public function createCustomer($data)
    {
        $url = $this->baseUrl . '/customer';
        
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->secretKey,
            'Content-Type' => 'application/json',
        ])->post($url, $data);

        return $response->json();
    }

    /**
     * Get public key
     */
    public function getPublicKey()
    {
        return $this->publicKey;
    }
} 
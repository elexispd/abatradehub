<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PaystackService
{
    protected ?string $secretKey;
    protected ?string $publicKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->secretKey = config('services.paystack.secret_key') ?: env('PAYSTACK_SECRET_KEY');
        $this->publicKey = config('services.paystack.public_key') ?: env('PAYSTACK_PUBLIC_KEY');
        $this->baseUrl = rtrim(config('services.paystack.base_url', 'https://api.paystack.co'), '/');
    }

    public function getPublicKey(): ?string
    {
        return $this->publicKey;
    }

    public function getSecretKey(): ?string
    {
        return $this->secretKey;
    }

    /**
     * Initialize a transaction with Paystack.
     *
     * @param  array{
     *     amount: int, // Amount in kobo (e.g. 500000 for ₦5,000)
     *     email: string,
     *     reference: string,
     *     callback_url?: string,
     *     currency?: string,
     *     metadata?: array,
     *     channels?: array
     * } $data
     * @return array
     */
    public function initializeTransaction(array $data): array
    {
        if (empty($this->secretKey)) {
            Log::error('Paystack secret key is missing in environment.');
            throw new RuntimeException('Paystack payment gateway is not properly configured.');
        }

        $url = "{$this->baseUrl}/transaction/initialize";

        try {
            $response = Http::withToken($this->secretKey)
                ->acceptJson()
                ->timeout(20)
                ->post($url, [
                    'amount' => $data['amount'],
                    'email' => $data['email'],
                    'reference' => $data['reference'],
                    'callback_url' => $data['callback_url'] ?? null,
                    'currency' => $data['currency'] ?? 'NGN',
                    'metadata' => $data['metadata'] ?? [],
                    'channels' => $data['channels'] ?? ['card', 'bank', 'ussd', 'qr', 'mobile_money', 'bank_transfer'],
                ]);

            $json = $response->json();

            if ($response->successful() && ! empty($json['status'])) {
                return [
                    'status' => true,
                    'message' => $json['message'] ?? 'Authorization URL created',
                    'data' => $json['data'],
                ];
            }

            Log::error('Paystack transaction initialization failed', [
                'status' => $response->status(),
                'response' => $json,
            ]);

            return [
                'status' => false,
                'message' => $json['message'] ?? 'Failed to initialize payment with Paystack.',
            ];
        } catch (\Throwable $e) {
            Log::error('Paystack connection exception during initialization: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'status' => false,
                'message' => 'Unable to connect to Paystack payment gateway. Please check your internet connection or try again.',
            ];
        }
    }

    /**
     * Verify a transaction with Paystack by reference.
     *
     * @param string $reference
     * @return array
     */
    public function verifyTransaction(string $reference): array
    {
        if (empty($this->secretKey)) {
            Log::error('Paystack secret key is missing in environment.');
            throw new RuntimeException('Paystack payment gateway is not properly configured.');
        }

        $url = "{$this->baseUrl}/transaction/verify/" . rawurlencode($reference);

        try {
            $response = Http::withToken($this->secretKey)
                ->acceptJson()
                ->timeout(20)
                ->get($url);

            $json = $response->json();

            if ($response->successful() && ! empty($json['status'])) {
                return [
                    'status' => true,
                    'message' => $json['message'] ?? 'Verification successful',
                    'data' => $json['data'],
                ];
            }

            Log::warning('Paystack verification failed', [
                'reference' => $reference,
                'status' => $response->status(),
                'response' => $json,
            ]);

            return [
                'status' => false,
                'message' => $json['message'] ?? 'Unable to verify payment reference.',
            ];
        } catch (\Throwable $e) {
            Log::error('Paystack connection exception during verification: ' . $e->getMessage(), [
                'reference' => $reference,
            ]);

            return [
                'status' => false,
                'message' => 'Unable to communicate with Paystack for verification.',
            ];
        }
    }

    /**
     * Verify Paystack webhook HMAC-SHA512 signature.
     *
     * @param string|null $signature
     * @param string $payload
     * @return bool
     */
    public function isValidWebhookSignature(?string $signature, string $payload): bool
    {
        if (empty($signature) || empty($this->secretKey)) {
            return false;
        }

        $expected = hash_hmac('sha512', $payload, $this->secretKey);

        return hash_equals($expected, $signature);
    }
}


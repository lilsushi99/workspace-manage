<?php

namespace App\Services\Payments\Adapters;

use App\Services\Payments\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class FlutterwavePaymentGateway implements PaymentGatewayInterface
{
    protected string $secretKey;
    protected string $baseUrl;

    public function __construct(?string $secretKey = null, ?string $baseUrl = null)
    {
        $this->secretKey = $secretKey ?: (string) config('services.flutterwave.secret_key', env('FLUTTERWAVE_SECRET_KEY', 'dummy_flw_secret_key'));
        $this->baseUrl = $baseUrl ?: (string) config('services.flutterwave.base_url', 'https://api.flutterwave.com/v3');
    }

    public function initializePayment(array $payload): array
    {
        $url = rtrim($this->baseUrl, '/') . '/payments';

        $params = [
            'tx_ref' => $payload['reference'],
            'amount' => (float) $payload['amount'],
            'currency' => $payload['currency'] ?? 'USD',
            'redirect_url' => $payload['redirect_url'],
            'customer' => [
                'email' => $payload['email'],
                'name' => $payload['name'] ?? 'Customer',
                'phonenumber' => $payload['phone'] ?? null,
            ],
            'customizations' => [
                'title' => $payload['title'] ?? 'SMM Order Payment',
                'description' => $payload['description'] ?? 'Payment for social media growth service',
            ],
            'meta' => [
                'order_id' => $payload['order_id'] ?? null,
                'tenant_id' => $payload['tenant_id'] ?? null,
            ],
        ];

        try {
            $response = Http::withToken($this->secretKey)->timeout(10)->post($url, $params);

            if ($response->failed()) {
                Log::error("Flutterwave initialization failed", ['status' => $response->status(), 'body' => $response->body()]);
                throw new RuntimeException("Unable to initialize Flutterwave payment session.");
            }

            $body = $response->json();

            if (($body['status'] ?? '') !== 'success' || empty($body['data']['link'])) {
                throw new RuntimeException($body['message'] ?? 'Flutterwave initialization returned invalid payload.');
            }

            return [
                'payment_url' => $body['data']['link'],
                'reference' => $payload['reference'],
                'raw_response' => $body,
            ];
        } catch (\Exception $e) {
            Log::error("Flutterwave payment exception", ['error' => $e->getMessage()]);
            throw new RuntimeException("Flutterwave payment gateway error: " . $e->getMessage());
        }
    }

    public function verifyTransaction(string $transactionId): array
    {
        $url = rtrim($this->baseUrl, '/') . "/transactions/{$transactionId}/verify";

        try {
            $response = Http::withToken($this->secretKey)->timeout(10)->get($url);

            if ($response->failed()) {
                Log::error("Flutterwave verification HTTP error", ['id' => $transactionId, 'status' => $response->status()]);
                throw new RuntimeException("Flutterwave verification request failed.");
            }

            $body = $response->json();

            if (($body['status'] ?? '') !== 'success' || empty($body['data'])) {
                throw new RuntimeException("Flutterwave verification failed: invalid status response.");
            }

            $data = $body['data'];

            return [
                'status' => $data['status'] ?? 'failed', // 'successful', 'failed'
                'reference' => $data['tx_ref'] ?? '',
                'provider_reference' => (string) ($data['id'] ?? $transactionId),
                'amount' => (float) ($data['amount'] ?? 0),
                'currency' => $data['currency'] ?? 'USD',
                'payment_method' => $data['payment_type'] ?? 'card',
                'paid_at' => isset($data['created_at']) ? date('Y-m-d H:i:s', strtotime($data['created_at'])) : now()->toDateTimeString(),
                'raw_response' => $body,
            ];
        } catch (\Exception $e) {
            Log::error("Flutterwave transaction verification error", ['id' => $transactionId, 'error' => $e->getMessage()]);
            throw new RuntimeException("Unable to verify Flutterwave transaction: " . $e->getMessage());
        }
    }
}

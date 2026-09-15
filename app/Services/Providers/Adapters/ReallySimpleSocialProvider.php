<?php

namespace App\Services\Providers\Adapters;

use App\Models\Provider;
use App\Services\Providers\Contracts\SmmProviderInterface;
use App\Services\Providers\Exceptions\ProviderAuthenticationException;
use App\Services\Providers\Exceptions\ProviderRateLimitException;
use App\Services\Providers\Exceptions\ProviderUnavailableException;
use App\Services\Providers\Exceptions\ProviderValidationException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ReallySimpleSocialProvider implements SmmProviderInterface
{
    protected Provider $provider;

    public function __construct(Provider $provider)
    {
        $this->provider = $provider;
    }

    public function getServices(): array
    {
        $response = $this->sendRequest(['action' => 'services']);

        if (!is_array($response)) {
            throw new ProviderValidationException("Invalid service payload format received from provider.");
        }

        return array_map(function ($s) {
            return [
                'external_service_id' => (string) ($s['service'] ?? $s['id'] ?? ''),
                'name' => $s['name'] ?? '',
                'category' => $s['category'] ?? 'General',
                'platform' => $s['platform'] ?? $this->detectPlatform($s['name'] ?? '', $s['category'] ?? ''),
                'description' => $s['description'] ?? null,
                'min_quantity' => (int) ($s['min'] ?? 1),
                'max_quantity' => (int) ($s['max'] ?? 1000000),
                'provider_price' => (float) ($s['rate'] ?? $s['price'] ?? 0),
                'provider_currency' => $s['currency'] ?? $this->provider->currency ?? 'USD',
                'supports_refill' => !empty($s['refill']),
                'supports_cancel' => !empty($s['cancel']),
                'supports_drip_feed' => !empty($s['dripfeed']),
                'raw_response' => $s,
            ];
        }, $response);
    }

    public function getBalance(): array
    {
        $response = $this->sendRequest(['action' => 'balance']);

        return [
            'balance' => (float) ($response['balance'] ?? 0.0),
            'currency' => $response['currency'] ?? $this->provider->currency ?? 'USD',
        ];
    }

    public function createOrder(array $payload): array
    {
        $requestData = array_merge([
            'action' => 'add',
            'service' => $payload['service_id'] ?? null,
            'link' => $payload['target'] ?? null,
            'quantity' => $payload['quantity'] ?? null,
        ], array_diff_key($payload, array_flip(['service_id', 'target', 'quantity'])));

        $response = $this->sendRequest($requestData);

        if (empty($response['order'])) {
            throw new ProviderValidationException($response['error'] ?? 'Failed to place provider order.');
        }

        return [
            'provider_order_id' => (string) $response['order'],
            'raw_response' => $response,
        ];
    }

    public function getOrderStatus(string $providerOrderId): array
    {
        $response = $this->sendRequest([
            'action' => 'status',
            'order' => $providerOrderId,
        ]);

        return [
            'status' => $response['status'] ?? 'pending',
            'charge' => (float) ($response['charge'] ?? 0),
            'start_counter' => (int) ($response['start_counter'] ?? 0),
            'remains' => (int) ($response['remains'] ?? 0),
            'currency' => $response['currency'] ?? $this->provider->currency ?? 'USD',
            'raw_response' => $response,
        ];
    }

    public function testConnection(): bool
    {
        try {
            $balance = $this->getBalance();
            return isset($balance['balance']);
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function sendRequest(array $params): array
    {
        $url = $this->provider->api_url;
        $params['key'] = $this->provider->api_key;

        try {
            $response = Http::timeout(10)->asForm()->post($url, $params);

            if ($response->status() === 401 || $response->status() === 403) {
                throw new ProviderAuthenticationException("Authentication failed for provider '{$this->provider->name}'.");
            }

            if ($response->status() === 429) {
                throw new ProviderRateLimitException("Rate limit exceeded for provider '{$this->provider->name}'.");
            }

            if ($response->failed()) {
                throw new ProviderUnavailableException("Provider '{$this->provider->name}' returned HTTP error {$response->status()}.");
            }

            $data = $response->json();

            if (isset($data['error'])) {
                if (str_contains(strtolower($data['error']), 'key') || str_contains(strtolower($data['error']), 'auth')) {
                    throw new ProviderAuthenticationException($data['error']);
                }
                throw new ProviderValidationException($data['error']);
            }

            return $data ?: [];
        } catch (\Exception $e) {
            if ($e instanceof ProviderAuthenticationException ||
                $e instanceof ProviderRateLimitException ||
                $e instanceof ProviderValidationException ||
                $e instanceof ProviderUnavailableException) {
                throw $e;
            }

            Log::error("Provider API request failed", [
                'provider_id' => $this->provider->id,
                'action' => $params['action'] ?? 'unknown',
                'error' => $e->getMessage(),
            ]);

            throw new ProviderUnavailableException("Unable to connect to provider '{$this->provider->name}': " . $e->getMessage());
        }
    }

    protected function detectPlatform(string $name, string $category): string
    {
        $text = strtolower($name . ' ' . $category);
        if (str_contains($text, 'instagram') || str_contains($text, 'ig')) return 'Instagram';
        if (str_contains($text, 'tiktok')) return 'TikTok';
        if (str_contains($text, 'youtube') || str_contains($text, 'yt')) return 'YouTube';
        if (str_contains($text, 'facebook') || str_contains($text, 'fb')) return 'Facebook';
        if (str_contains($text, 'twitter') || str_contains($text, 'x')) return 'X';
        if (str_contains($text, 'telegram') || str_contains($text, 'tg')) return 'Telegram';
        return 'General';
    }
}

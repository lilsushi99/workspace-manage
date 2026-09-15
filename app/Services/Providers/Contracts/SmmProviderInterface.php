<?php

namespace App\Services\Providers\Contracts;

interface SmmProviderInterface
{
    public function getServices(): array;

    public function getBalance(): array;

    public function createOrder(array $payload): array;

    public function getOrderStatus(string $providerOrderId): array;

    public function testConnection(): bool;
}

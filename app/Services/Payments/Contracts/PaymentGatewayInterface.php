<?php

namespace App\Services\Payments\Contracts;

interface PaymentGatewayInterface
{
    public function initializePayment(array $payload): array;

    public function verifyTransaction(string $transactionId): array;
}

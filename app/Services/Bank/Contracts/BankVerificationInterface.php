<?php

namespace App\Services\Bank\Contracts;

interface BankVerificationInterface
{
    public function resolveAccountName(string $bankCode, string $accountNumber): array;
}

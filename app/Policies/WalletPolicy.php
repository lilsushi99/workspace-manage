<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Wallet;

class WalletPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    public function view(User $user, Wallet $wallet): bool
    {
        return $user->tenant && $wallet->tenant_id === $user->tenant->id;
    }

    public function update(User $user, Wallet $wallet): bool
    {
        return $user->tenant && $wallet->tenant_id === $user->tenant->id;
    }
}

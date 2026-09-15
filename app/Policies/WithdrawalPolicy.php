<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Withdrawal;

class WithdrawalPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->tenant !== null;
    }

    public function view(User $user, Withdrawal $withdrawal): bool
    {
        return $user->tenant && $withdrawal->tenant_id === $user->tenant->id;
    }

    public function create(User $user): bool
    {
        return $user->tenant !== null;
    }
}

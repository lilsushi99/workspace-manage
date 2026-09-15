<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;

class CustomerPolicy
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

    public function view(User $user, Customer $customer): bool
    {
        return $user->tenant && $customer->tenant_id === $user->tenant->id;
    }

    public function create(User $user): bool
    {
        return $user->tenant !== null;
    }

    public function update(User $user, Customer $customer): bool
    {
        return $user->tenant && $customer->tenant_id === $user->tenant->id;
    }

    public function delete(User $user, Customer $customer): bool
    {
        return $user->tenant && $customer->tenant_id === $user->tenant->id;
    }
}

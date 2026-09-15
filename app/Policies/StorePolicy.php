<?php

namespace App\Policies;

use App\Models\Store;
use App\Models\User;

class StorePolicy
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

    public function view(User $user, Store $store): bool
    {
        return $user->tenant && $store->tenant_id === $user->tenant->id;
    }

    public function create(User $user): bool
    {
        return $user->tenant !== null;
    }

    public function update(User $user, Store $store): bool
    {
        return $user->tenant && $store->tenant_id === $user->tenant->id;
    }

    public function delete(User $user, Store $store): bool
    {
        return $user->tenant && $store->tenant_id === $user->tenant->id;
    }
}

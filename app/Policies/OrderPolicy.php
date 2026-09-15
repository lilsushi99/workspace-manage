<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
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

    public function view(User $user, Order $order): bool
    {
        return $user->tenant && $order->tenant_id === $user->tenant->id;
    }

    public function create(User $user): bool
    {
        return $user->tenant !== null;
    }

    public function update(User $user, Order $order): bool
    {
        return $user->tenant && $order->tenant_id === $user->tenant->id;
    }

    public function delete(User $user, Order $order): bool
    {
        return $user->tenant && $order->tenant_id === $user->tenant->id;
    }
}

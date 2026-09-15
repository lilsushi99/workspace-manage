<?php

namespace App\Policies;

use App\Models\TenantService;
use App\Models\User;

class TenantServicePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    public function view(User $user, TenantService $tenantService): bool
    {
        return $user->tenant && $tenantService->tenant_id === $user->tenant->id;
    }

    public function update(User $user, TenantService $tenantService): bool
    {
        return $user->tenant && $tenantService->tenant_id === $user->tenant->id;
    }

    public function delete(User $user, TenantService $tenantService): bool
    {
        return $user->tenant && $tenantService->tenant_id === $user->tenant->id;
    }
}

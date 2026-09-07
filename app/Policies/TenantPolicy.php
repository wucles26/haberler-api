<?php

namespace App\Policies;

use App\Enums\TenantRole;
use App\Models\Tenant;
use App\Models\User;

class TenantPolicy
{
    /**
     * Determine whether the user can view the tenant dashboard.
     */
    public function view(User $user, Tenant $tenant): bool
    {
        return $user->belongsToTenant($tenant);
    }

    /**
     * Determine whether the user can manage tenant members.
     */
    public function manageMembers(User $user, Tenant $tenant): bool
    {
        return $user->hasRoleInTenant($tenant, TenantRole::Admin);
    }
}

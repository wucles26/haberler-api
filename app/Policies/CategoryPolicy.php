<?php

namespace App\Policies;

use App\Enums\TenantRole;
use App\Models\Category;
use App\Models\Tenant;
use App\Models\User;

class CategoryPolicy
{
    public function viewAny(User $user, Tenant $tenant): bool
    {
        return $user->hasRoleInTenant($tenant, TenantRole::Admin, TenantRole::Editor);
    }

    public function view(User $user, Category $category): bool
    {
        return $this->viewAny($user, $category->tenant);
    }

    public function create(User $user, Tenant $tenant): bool
    {
        return $this->viewAny($user, $tenant);
    }

    public function update(User $user, Category $category): bool
    {
        return $this->viewAny($user, $category->tenant);
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->hasRoleInTenant($category->tenant, TenantRole::Admin);
    }
}

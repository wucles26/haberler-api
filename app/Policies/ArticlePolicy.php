<?php

namespace App\Policies;

use App\Enums\TenantRole;
use App\Models\Article;
use App\Models\Tenant;
use App\Models\User;

class ArticlePolicy
{
    public function viewAny(User $user, Tenant $tenant): bool
    {
        return $user->hasRoleInTenant($tenant, TenantRole::Admin, TenantRole::Editor);
    }

    public function view(User $user, Article $article): bool
    {
        return $this->viewAny($user, $article->tenant);
    }

    public function create(User $user, Tenant $tenant): bool
    {
        return $this->viewAny($user, $tenant);
    }

    public function update(User $user, Article $article): bool
    {
        return $this->viewAny($user, $article->tenant);
    }

    public function delete(User $user, Article $article): bool
    {
        return $user->hasRoleInTenant($article->tenant, TenantRole::Admin);
    }
}

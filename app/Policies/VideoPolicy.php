<?php

namespace App\Policies;

use App\Enums\TenantRole;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Video;

class VideoPolicy
{
    public function viewAny(User $user, Tenant $tenant): bool
    {
        return $user->hasRoleInTenant($tenant, TenantRole::Admin, TenantRole::Editor);
    }

    public function view(User $user, Video $video): bool
    {
        return $this->viewAny($user, $video->tenant);
    }

    public function create(User $user, Tenant $tenant): bool
    {
        return $this->viewAny($user, $tenant);
    }

    public function update(User $user, Video $video): bool
    {
        return $this->viewAny($user, $video->tenant);
    }

    public function delete(User $user, Video $video): bool
    {
        return $user->hasRoleInTenant($video->tenant, TenantRole::Admin);
    }
}

<?php

namespace App\Policies;

use App\Enums\TenantRole;
use App\Models\PhotoGallery;
use App\Models\Tenant;
use App\Models\User;

class PhotoGalleryPolicy
{
    public function viewAny(User $user, Tenant $tenant): bool
    {
        return $user->hasRoleInTenant($tenant, TenantRole::Admin, TenantRole::Editor);
    }

    public function view(User $user, PhotoGallery $photoGallery): bool
    {
        return $this->viewAny($user, $photoGallery->tenant);
    }

    public function create(User $user, Tenant $tenant): bool
    {
        return $this->viewAny($user, $tenant);
    }

    public function update(User $user, PhotoGallery $photoGallery): bool
    {
        return $this->viewAny($user, $photoGallery->tenant);
    }

    public function delete(User $user, PhotoGallery $photoGallery): bool
    {
        return $user->hasRoleInTenant($photoGallery->tenant, TenantRole::Admin);
    }
}

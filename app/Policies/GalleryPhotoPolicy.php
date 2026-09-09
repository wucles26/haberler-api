<?php

namespace App\Policies;

use App\Enums\TenantRole;
use App\Models\GalleryPhoto;
use App\Models\User;

class GalleryPhotoPolicy
{
    public function view(User $user, GalleryPhoto $galleryPhoto): bool
    {
        return $user->hasRoleInTenant($galleryPhoto->gallery->tenant, TenantRole::Admin, TenantRole::Editor);
    }

    public function update(User $user, GalleryPhoto $galleryPhoto): bool
    {
        return $this->view($user, $galleryPhoto);
    }

    public function delete(User $user, GalleryPhoto $galleryPhoto): bool
    {
        return $user->hasRoleInTenant($galleryPhoto->gallery->tenant, TenantRole::Admin);
    }
}

<?php

namespace App\Models;

use App\Enums\TenantRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @return BelongsToMany<Tenant, $this>
     */
    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class)
            ->using(TenantUser::class)
            ->withPivot('id', 'role')
            ->withTimestamps();
    }

    /**
     * @return HasMany<TenantUser, $this>
     */
    public function memberships(): HasMany
    {
        return $this->hasMany(TenantUser::class);
    }

    public function membershipFor(Tenant $tenant): ?TenantUser
    {
        if ($this->relationLoaded('memberships')) {
            return $this->memberships->firstWhere('tenant_id', $tenant->id);
        }

        return $this->memberships()
            ->where('tenant_id', $tenant->id)
            ->first();
    }

    public function roleIn(Tenant $tenant): ?TenantRole
    {
        return $this->membershipFor($tenant)?->role;
    }

    public function belongsToTenant(Tenant $tenant): bool
    {
        return $this->membershipFor($tenant) !== null;
    }

    public function hasRoleInTenant(Tenant $tenant, TenantRole ...$roles): bool
    {
        $role = $this->roleIn($tenant);

        if ($role === null) {
            return false;
        }

        foreach ($roles as $allowedRole) {
            if ($role === $allowedRole) {
                return true;
            }
        }

        return false;
    }

    public function isAdminOf(Tenant $tenant): bool
    {
        return $this->hasRoleInTenant($tenant, TenantRole::Admin);
    }

    public function defaultTenant(): ?Tenant
    {
        return $this->tenants()->orderBy('tenants.id')->first();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}

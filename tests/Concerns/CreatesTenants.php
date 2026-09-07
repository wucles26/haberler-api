<?php

namespace Tests\Concerns;

use App\Enums\TenantRole;
use App\Models\Tenant;
use App\Models\User;

trait CreatesTenants
{
    /**
     * @return array{0: User, 1: Tenant}
     */
    protected function createUserWithTenant(TenantRole $role = TenantRole::Admin): array
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create();

        $tenant->users()->attach($user->id, [
            'role' => $role->value,
        ]);

        return [$user, $tenant];
    }
}

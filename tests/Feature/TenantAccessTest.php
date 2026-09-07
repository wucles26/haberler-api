<?php

namespace Tests\Feature;

use App\Enums\TenantRole;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTenants;
use Tests\TestCase;

class TenantAccessTest extends TestCase
{
    use CreatesTenants;
    use RefreshDatabase;

    public function test_member_can_view_tenant_dashboard(): void
    {
        [$user, $tenant] = $this->createUserWithTenant(TenantRole::Editor);

        $this->actingAs($user)
            ->get(route('tenant.dashboard', $tenant))
            ->assertOk()
            ->assertSee($tenant->name)
            ->assertSee('Editor');
    }

    public function test_non_member_cannot_view_tenant_dashboard(): void
    {
        [$user] = $this->createUserWithTenant();
        $otherTenant = Tenant::factory()->create();

        $this->actingAs($user)
            ->get(route('tenant.dashboard', $otherTenant))
            ->assertForbidden();
    }

    public function test_dashboard_redirects_to_default_tenant(): void
    {
        [$user, $tenant] = $this->createUserWithTenant();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('tenant.dashboard', $tenant));
    }

    public function test_author_cannot_access_member_management(): void
    {
        [$user, $tenant] = $this->createUserWithTenant(TenantRole::Author);

        $this->actingAs($user)
            ->get(route('tenant.members', $tenant))
            ->assertForbidden();
    }

    public function test_admin_can_access_member_management(): void
    {
        [$user, $tenant] = $this->createUserWithTenant(TenantRole::Admin);

        $this->actingAs($user)
            ->get(route('tenant.members', $tenant))
            ->assertOk()
            ->assertSee(__('Current members'));
    }

    public function test_guest_is_redirected_from_tenant_dashboard(): void
    {
        $tenant = Tenant::factory()->create();

        $this->get(route('tenant.dashboard', $tenant))
            ->assertRedirect(route('login'));
    }
}

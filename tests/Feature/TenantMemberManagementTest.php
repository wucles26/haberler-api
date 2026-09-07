<?php

namespace Tests\Feature;

use App\Enums\TenantRole;
use App\Livewire\Tenants\ManageMembers;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\CreatesTenants;
use Tests\TestCase;

class TenantMemberManagementTest extends TestCase
{
    use CreatesTenants;
    use RefreshDatabase;

    public function test_admin_can_add_member_by_email(): void
    {
        [$admin, $tenant] = $this->createUserWithTenant(TenantRole::Admin);
        $member = User::factory()->create([
            'email' => 'editor@example.com',
        ]);

        Livewire::actingAs($admin)
            ->test(ManageMembers::class, ['tenant' => $tenant])
            ->set('email', 'editor@example.com')
            ->set('role', TenantRole::Editor->value)
            ->call('addMember')
            ->assertHasNoErrors();

        $this->assertTrue($member->fresh()->belongsToTenant($tenant));
        $this->assertSame(TenantRole::Editor, $member->fresh()->roleIn($tenant));
    }

    public function test_admin_can_update_member_role(): void
    {
        [$admin, $tenant] = $this->createUserWithTenant(TenantRole::Admin);
        $member = User::factory()->create();

        $tenant->users()->attach($member->id, [
            'role' => TenantRole::Author->value,
        ]);

        $membershipId = $member->membershipFor($tenant)->id;

        Livewire::actingAs($admin)
            ->test(ManageMembers::class, ['tenant' => $tenant])
            ->call('updateRole', $membershipId, TenantRole::Editor->value)
            ->assertHasNoErrors();

        $this->assertSame(TenantRole::Editor, $member->fresh()->roleIn($tenant));
    }

    public function test_admin_cannot_remove_the_last_admin(): void
    {
        [$admin, $tenant] = $this->createUserWithTenant(TenantRole::Admin);
        $membershipId = $admin->membershipFor($tenant)->id;

        Livewire::actingAs($admin)
            ->test(ManageMembers::class, ['tenant' => $tenant])
            ->call('removeMember', $membershipId)
            ->assertHasErrors(['members']);

        $this->assertTrue($admin->fresh()->belongsToTenant($tenant));
    }

    public function test_admin_can_remove_another_member(): void
    {
        [$admin, $tenant] = $this->createUserWithTenant(TenantRole::Admin);
        $member = User::factory()->create();

        $tenant->users()->attach($member->id, [
            'role' => TenantRole::Author->value,
        ]);

        $membershipId = $member->membershipFor($tenant)->id;

        Livewire::actingAs($admin)
            ->test(ManageMembers::class, ['tenant' => $tenant])
            ->call('removeMember', $membershipId)
            ->assertHasNoErrors();

        $this->assertFalse($member->fresh()->belongsToTenant($tenant));
    }
}

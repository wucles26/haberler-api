<?php

namespace App\Livewire\Tenants;

use App\Enums\TenantRole;
use App\Models\Tenant;
use App\Models\TenantUser;
use App\Models\User;
use App\Support\CurrentTenant;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ManageMembers extends Component
{
    public Tenant $tenant;

    public string $email = '';

    public string $role = '';

    public function mount(Tenant $tenant, CurrentTenant $currentTenant): void
    {
        $this->tenant = $tenant;
        $this->authorize('manageMembers', $tenant);
        $currentTenant->set($tenant);
        $this->role = TenantRole::Author->value;
    }

    public function addMember(): void
    {
        $this->authorize('manageMembers', $this->tenant);

        $validated = $this->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'role' => ['required', Rule::enum(TenantRole::class)],
        ]);

        $user = User::query()->where('email', $validated['email'])->firstOrFail();

        if ($user->belongsToTenant($this->tenant)) {
            $this->addError('email', __('This user is already a member of the publication.'));

            return;
        }

        $this->tenant->users()->attach($user->id, [
            'role' => $validated['role'],
        ]);

        $this->reset('email');
        $this->role = TenantRole::Author->value;

        $this->dispatch('member-added');
    }

    public function updateRole(int $membershipId, string $role): void
    {
        $this->authorize('manageMembers', $this->tenant);

        $validatedRole = TenantRole::from($role);
        $membership = $this->membership($membershipId);

        if ($this->isLastAdmin($membership) && $validatedRole !== TenantRole::Admin) {
            $this->addError('members', __('The publication must keep at least one admin.'));

            return;
        }

        $membership->update([
            'role' => $validatedRole,
        ]);
    }

    public function removeMember(int $membershipId): void
    {
        $this->authorize('manageMembers', $this->tenant);

        $membership = $this->membership($membershipId);

        if ($membership->user_id === auth()->id()) {
            $this->addError('members', __('You cannot remove yourself from the publication.'));

            return;
        }

        if ($this->isLastAdmin($membership)) {
            $this->addError('members', __('The publication must keep at least one admin.'));

            return;
        }

        $membership->delete();
    }

    public function render(): View
    {
        return view('livewire.tenants.manage-members', [
            'memberships' => $this->tenant->memberships()
                ->with('user')
                ->orderBy('id')
                ->get(),
            'roles' => TenantRole::cases(),
        ]);
    }

    protected function membership(int $membershipId): TenantUser
    {
        return $this->tenant->memberships()
            ->whereKey($membershipId)
            ->firstOrFail();
    }

    protected function isLastAdmin(TenantUser $membership): bool
    {
        if ($membership->role !== TenantRole::Admin) {
            return false;
        }

        return $this->tenant->memberships()
            ->where('role', TenantRole::Admin->value)
            ->count() === 1;
    }
}

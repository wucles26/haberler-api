<?php

namespace App\Livewire\Tenants;

use App\Models\Tenant;
use App\Support\CurrentTenant;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    public Tenant $tenant;

    public function mount(Tenant $tenant, CurrentTenant $currentTenant): void
    {
        $this->tenant = $tenant;
        $this->authorize('view', $tenant);
        $currentTenant->set($tenant);
    }

    public function render(): View
    {
        $membership = auth()->user()->membershipFor($this->tenant);

        return view('livewire.tenants.dashboard', [
            'membership' => $membership,
            'memberCount' => $this->tenant->users()->count(),
        ]);
    }
}

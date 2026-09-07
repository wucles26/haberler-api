<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RedirectToTenantDashboardController extends Controller
{
    /**
     * Redirect authenticated users to their default tenant dashboard.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $tenant = $user->defaultTenant();

        abort_unless($tenant !== null, 403, 'No publication membership found.');

        return redirect()->route('tenant.dashboard', $tenant);
    }
}

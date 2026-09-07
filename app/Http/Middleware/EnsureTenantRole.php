<?php

namespace App\Http\Middleware;

use App\Enums\TenantRole;
use App\Support\CurrentTenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantRole
{
    public function __construct(public CurrentTenant $currentTenant) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $tenant = $this->currentTenant->get() ?? $request->route('tenant');
        $user = $request->user();

        abort_unless($tenant !== null && $user !== null, 403);

        $allowedRoles = array_map(
            fn (string $role): TenantRole => TenantRole::from($role),
            $roles,
        );

        abort_unless($user->hasRoleInTenant($tenant, ...$allowedRoles), 403);

        return $next($request);
    }
}

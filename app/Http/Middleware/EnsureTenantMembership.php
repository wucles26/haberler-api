<?php

namespace App\Http\Middleware;

use App\Support\CurrentTenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantMembership
{
    public function __construct(public CurrentTenant $currentTenant) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $request->route('tenant');

        abort_unless($tenant !== null, 404);

        $user = $request->user();

        abort_unless($user !== null && $user->belongsToTenant($tenant), 403);

        $this->currentTenant->set($tenant);

        return $next($request);
    }
}

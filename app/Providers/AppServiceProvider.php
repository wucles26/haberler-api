<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Tenant;
use App\Policies\CategoryPolicy;
use App\Policies\TenantPolicy;
use App\Support\CurrentTenant;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(CurrentTenant::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Tenant::class, TenantPolicy::class);
    }
}

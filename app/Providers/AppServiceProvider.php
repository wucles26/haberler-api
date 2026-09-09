<?php

namespace App\Providers;

use App\Enums\TenantRole;
use App\Models\Article;
use App\Models\Category;
use App\Models\GalleryPhoto;
use App\Models\PhotoGallery;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Video;
use App\Policies\ArticlePolicy;
use App\Policies\CategoryPolicy;
use App\Policies\GalleryPhotoPolicy;
use App\Policies\PhotoGalleryPolicy;
use App\Policies\TenantPolicy;
use App\Policies\VideoPolicy;
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
        Gate::policy(Article::class, ArticlePolicy::class);
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(GalleryPhoto::class, GalleryPhotoPolicy::class);
        Gate::policy(PhotoGallery::class, PhotoGalleryPolicy::class);
        Gate::policy(Tenant::class, TenantPolicy::class);
        Gate::policy(Video::class, VideoPolicy::class);
        Gate::define('managePhotoGalleries', fn (User $user, Tenant $tenant): bool => $user->hasRoleInTenant($tenant, TenantRole::Admin, TenantRole::Editor));
        Gate::define('manageVideos', fn (User $user, Tenant $tenant): bool => $user->hasRoleInTenant($tenant, TenantRole::Admin, TenantRole::Editor));
    }
}

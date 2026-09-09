<?php

use App\Http\Controllers\RedirectToTenantDashboardController;
use App\Livewire\Tenants\Dashboard;
use App\Livewire\Tenants\ManageArticles;
use App\Livewire\Tenants\ManageCategories;
use App\Livewire\Tenants\ManageMembers;
use App\Livewire\Tenants\ManagePhotoGalleries;
use App\Livewire\Tenants\ManageVideos;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', RedirectToTenantDashboardController::class)
        ->name('dashboard');

    Route::prefix('t/{tenant:slug}')
        ->middleware(['tenant'])
        ->name('tenant.')
        ->group(function () {
            Route::get('dashboard', Dashboard::class)->name('dashboard');

            Route::get('members', ManageMembers::class)
                ->middleware('tenant.role:admin')
                ->name('members');

            Route::get('categories', ManageCategories::class)
                ->middleware('tenant.role:admin,editor')
                ->name('categories');

            Route::get('articles', ManageArticles::class)
                ->middleware('tenant.role:admin,editor')
                ->name('articles');

            Route::get('photo-galleries', ManagePhotoGalleries::class)
                ->middleware('tenant.role:admin,editor')
                ->name('photo-galleries');

            Route::get('videos', ManageVideos::class)
                ->middleware('tenant.role:admin,editor')
                ->name('videos');
        });
});

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';

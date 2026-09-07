<?php

use App\Http\Controllers\RedirectToTenantDashboardController;
use App\Livewire\Tenants\Dashboard;
use App\Livewire\Tenants\ManageMembers;
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
        });
});

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';

<?php

namespace Tests\Feature;

use App\Enums\TenantRole;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class TenantRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_tenant_and_admin_membership(): void
    {
        Volt::test('pages.auth.register')
            ->set('name', 'Selcuk')
            ->set('email', 'selcuk@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->set('publication_name', 'Ankara Haber')
            ->call('register')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tenants', [
            'name' => 'Ankara Haber',
            'slug' => 'ankara-haber',
        ]);

        $user = User::query()->where('email', 'selcuk@example.com')->first();
        $tenant = Tenant::query()->where('slug', 'ankara-haber')->first();

        $this->assertNotNull($user);
        $this->assertNotNull($tenant);
        $this->assertTrue($user->isAdminOf($tenant));
        $this->assertSame(TenantRole::Admin, $user->roleIn($tenant));
    }

    public function test_registration_requires_publication_name(): void
    {
        Volt::test('pages.auth.register')
            ->set('name', 'Selcuk')
            ->set('email', 'selcuk@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register')
            ->assertHasErrors(['publication_name']);

        $this->assertGuest();
        $this->assertDatabaseCount('tenants', 0);
    }
}

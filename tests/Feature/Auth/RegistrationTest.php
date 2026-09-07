<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response
            ->assertOk()
            ->assertSeeVolt('pages.auth.register');
    }

    public function test_new_users_can_register(): void
    {
        $component = Volt::test('pages.auth.register')
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->set('publication_name', 'Demo Haber');

        $component->call('register');

        $this->assertAuthenticated();

        $user = auth()->user();
        $tenant = $user->defaultTenant();

        $this->assertNotNull($tenant);
        $this->assertSame('Demo Haber', $tenant->name);
        $this->assertTrue($user->isAdminOf($tenant));

        $component->assertRedirect(route('tenant.dashboard', $tenant, absolute: false));
    }
}

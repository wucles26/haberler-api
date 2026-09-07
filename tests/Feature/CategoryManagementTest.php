<?php

namespace Tests\Feature;

use App\Enums\TenantRole;
use App\Livewire\Tenants\ManageCategories;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\CreatesTenants;
use Tests\TestCase;

class CategoryManagementTest extends TestCase
{
    use CreatesTenants;
    use RefreshDatabase;

    public function test_admin_can_create_category_with_automatic_seo_values(): void
    {
        [$admin, $tenant] = $this->createUserWithTenant();

        Livewire::actingAs($admin)
            ->test(ManageCategories::class, ['tenant' => $tenant])
            ->set('name', 'Gündem')
            ->set('description', 'Güncel gelişmeler')
            ->set('sort_order', 1)
            ->call('saveCategory')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('categories', [
            'tenant_id' => $tenant->id,
            'name' => 'Gündem',
            'slug' => 'gundem',
            'meta_title' => 'Gündem Haberleri',
            'meta_description' => 'Gündem kategorisindeki son dakika haberleri ve güncel gelişmeler.',
            'sort_order' => 1,
        ]);
    }

    public function test_user_can_override_automatic_seo_values(): void
    {
        [$admin, $tenant] = $this->createUserWithTenant();

        Livewire::actingAs($admin)
            ->test(ManageCategories::class, ['tenant' => $tenant])
            ->set('name', 'Spor')
            ->set('meta_title', 'Özel spor başlığı')
            ->set('meta_description', 'Özel spor açıklaması')
            ->set('slug', 'ozel-spor')
            ->set('name', 'Spor Haberleri')
            ->call('saveCategory')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('categories', [
            'tenant_id' => $tenant->id,
            'slug' => 'ozel-spor',
            'meta_title' => 'Özel spor başlığı',
            'meta_description' => 'Özel spor açıklaması',
        ]);
    }

    public function test_editor_can_update_and_toggle_category(): void
    {
        [$editor, $tenant] = $this->createUserWithTenant(TenantRole::Editor);
        $category = Category::factory()->for($tenant)->create();

        Livewire::actingAs($editor)
            ->test(ManageCategories::class, ['tenant' => $tenant])
            ->call('editCategory', $category->id)
            ->set('name', 'Yeni kategori')
            ->set('slug', 'yeni-kategori')
            ->call('saveCategory')
            ->call('toggleActive', $category->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Yeni kategori',
            'slug' => 'yeni-kategori',
            'is_active' => false,
        ]);
    }

    public function test_admin_can_delete_category(): void
    {
        [$admin, $tenant] = $this->createUserWithTenant();
        $category = Category::factory()->for($tenant)->create();

        Livewire::actingAs($admin)
            ->test(ManageCategories::class, ['tenant' => $tenant])
            ->call('deleteCategory', $category->id)
            ->assertHasNoErrors();

        $this->assertModelMissing($category);
    }

    public function test_author_cannot_manage_categories(): void
    {
        [$author, $tenant] = $this->createUserWithTenant(TenantRole::Author);

        $this->actingAs($author)
            ->get(route('tenant.categories', $tenant))
            ->assertForbidden();
    }

    public function test_user_cannot_access_categories_from_another_tenant(): void
    {
        [$admin] = $this->createUserWithTenant();
        [, $otherTenant] = $this->createUserWithTenant();

        $this->actingAs($admin)
            ->get(route('tenant.categories', $otherTenant))
            ->assertForbidden();
    }
}

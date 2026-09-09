<?php

namespace Tests\Feature;

use App\Enums\TenantRole;
use App\Livewire\Tenants\ManageArticles;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\CreatesTenants;
use Tests\TestCase;

class ArticleManagementTest extends TestCase
{
    use CreatesTenants;
    use RefreshDatabase;

    public function test_admin_can_create_article_with_primary_and_additional_categories(): void
    {
        [$admin, $tenant] = $this->createUserWithTenant();
        $primary = Category::factory()->for($tenant)->create(['name' => 'Gündem']);
        $secondary = Category::factory()->for($tenant)->create(['name' => 'Politika']);

        Livewire::actingAs($admin)
            ->test(ManageArticles::class, ['tenant' => $tenant])
            ->set('primary_category_id', $primary->id)
            ->set('category_ids', [$secondary->id])
            ->set('title', 'Seçim sonuçları açıklandı')
            ->set('short_description', 'Kısa özet')
            ->set('body', '<p>Haber metni</p>')
            ->set('published_at', '2026-09-09T10:00')
            ->set('is_featured', true)
            ->call('saveArticle')
            ->assertHasNoErrors();

        $article = Article::query()->where('tenant_id', $tenant->id)->first();

        $this->assertNotNull($article);
        $this->assertSame('secim-sonuclari-aciklandi', $article->slug);
        $this->assertSame('Seçim sonuçları açıklandı', $article->meta_title);
        $this->assertSame('Kısa özet', $article->meta_description);
        $this->assertTrue($article->is_featured);
        $this->assertSame($primary->id, $article->primary_category_id);
        $this->assertEqualsCanonicalizing(
            [$primary->id, $secondary->id],
            $article->categories()->pluck('categories.id')->all()
        );
    }

    public function test_user_can_override_automatic_seo_values(): void
    {
        [$admin, $tenant] = $this->createUserWithTenant();
        $category = Category::factory()->for($tenant)->create();

        Livewire::actingAs($admin)
            ->test(ManageArticles::class, ['tenant' => $tenant])
            ->set('primary_category_id', $category->id)
            ->set('title', 'Otomatik başlık')
            ->set('meta_title', 'Özel SEO başlığı')
            ->set('meta_description', 'Özel SEO açıklaması')
            ->set('slug', 'ozel-slug')
            ->set('title', 'Güncellenmiş başlık')
            ->set('body', '<p>İçerik</p>')
            ->call('saveArticle')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('articles', [
            'tenant_id' => $tenant->id,
            'slug' => 'ozel-slug',
            'meta_title' => 'Özel SEO başlığı',
            'meta_description' => 'Özel SEO açıklaması',
        ]);
    }

    public function test_unsafe_html_is_sanitized_from_article_body(): void
    {
        [$admin, $tenant] = $this->createUserWithTenant();
        $category = Category::factory()->for($tenant)->create();

        Livewire::actingAs($admin)
            ->test(ManageArticles::class, ['tenant' => $tenant])
            ->set('primary_category_id', $category->id)
            ->set('title', 'Güvenlik testi')
            ->set('body', '<p>Temiz</p><script>alert(1)</script><a href="javascript:alert(1)">tıkla</a><strong onclick="alert(1)">kalın</strong>')
            ->call('saveArticle')
            ->assertHasNoErrors();

        $article = Article::query()->where('tenant_id', $tenant->id)->firstOrFail();

        $this->assertStringContainsString('<p>Temiz</p>', $article->body);
        $this->assertStringContainsString('<strong>kalın</strong>', $article->body);
        $this->assertStringNotContainsString('<script>', $article->body);
        $this->assertStringNotContainsString('javascript:', $article->body);
        $this->assertStringNotContainsString('onclick', $article->body);
    }

    public function test_editor_can_update_toggle_active_and_featured(): void
    {
        [$editor, $tenant] = $this->createUserWithTenant(TenantRole::Editor);
        $primary = Category::factory()->for($tenant)->create();
        $extra = Category::factory()->for($tenant)->create();
        $article = Article::factory()->for($tenant)->create([
            'primary_category_id' => $primary->id,
            'is_featured' => false,
        ]);
        $article->categories()->sync([$primary->id]);

        Livewire::actingAs($editor)
            ->test(ManageArticles::class, ['tenant' => $tenant])
            ->call('editArticle', $article->id)
            ->set('title', 'Güncellenen haber')
            ->set('slug', 'guncellenen-haber')
            ->set('primary_category_id', $primary->id)
            ->set('category_ids', [$extra->id])
            ->set('body', '<p>Yeni metin</p>')
            ->call('saveArticle')
            ->call('toggleActive', $article->id)
            ->call('toggleFeatured', $article->id)
            ->assertHasNoErrors();

        $article->refresh();

        $this->assertSame('Güncellenen haber', $article->title);
        $this->assertFalse($article->is_active);
        $this->assertTrue($article->is_featured);
        $this->assertEqualsCanonicalizing(
            [$primary->id, $extra->id],
            $article->categories()->pluck('categories.id')->all()
        );
    }

    public function test_multiple_articles_can_be_featured(): void
    {
        [$admin, $tenant] = $this->createUserWithTenant();
        $category = Category::factory()->for($tenant)->create();

        $first = Article::factory()->for($tenant)->create([
            'primary_category_id' => $category->id,
            'is_featured' => true,
        ]);
        $second = Article::factory()->for($tenant)->create([
            'primary_category_id' => $category->id,
            'is_featured' => false,
        ]);

        Livewire::actingAs($admin)
            ->test(ManageArticles::class, ['tenant' => $tenant])
            ->call('toggleFeatured', $second->id)
            ->assertHasNoErrors();

        $this->assertTrue($first->fresh()->is_featured);
        $this->assertTrue($second->fresh()->is_featured);
        $this->assertSame(2, $tenant->articles()->where('is_featured', true)->count());
    }

    public function test_admin_can_delete_article(): void
    {
        [$admin, $tenant] = $this->createUserWithTenant();
        $category = Category::factory()->for($tenant)->create();
        $article = Article::factory()->for($tenant)->create([
            'primary_category_id' => $category->id,
        ]);

        Livewire::actingAs($admin)
            ->test(ManageArticles::class, ['tenant' => $tenant])
            ->call('deleteArticle', $article->id)
            ->assertHasNoErrors();

        $this->assertModelMissing($article);
    }

    public function test_article_rejects_category_from_another_tenant(): void
    {
        [$admin, $tenant] = $this->createUserWithTenant();
        [, $otherTenant] = $this->createUserWithTenant();
        $foreignCategory = Category::factory()->for($otherTenant)->create();

        Livewire::actingAs($admin)
            ->test(ManageArticles::class, ['tenant' => $tenant])
            ->set('primary_category_id', $foreignCategory->id)
            ->set('title', 'Geçersiz kategori')
            ->set('body', '<p>Metin</p>')
            ->call('saveArticle')
            ->assertHasErrors(['primary_category_id']);
    }

    public function test_author_cannot_manage_articles(): void
    {
        [$author, $tenant] = $this->createUserWithTenant(TenantRole::Author);

        $this->actingAs($author)
            ->get(route('tenant.articles', $tenant))
            ->assertForbidden();
    }

    public function test_user_cannot_access_articles_from_another_tenant(): void
    {
        [$admin] = $this->createUserWithTenant();
        [, $otherTenant] = $this->createUserWithTenant();

        $this->actingAs($admin)
            ->get(route('tenant.articles', $otherTenant))
            ->assertForbidden();
    }
}

<?php

namespace App\Livewire\Tenants;

use App\Models\Article;
use App\Models\Tenant;
use App\Support\CurrentTenant;
use App\Support\HtmlSanitizer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ManageArticles extends Component
{
    public Tenant $tenant;

    public ?int $editingArticleId = null;

    public ?int $primary_category_id = null;

    /** @var list<int> */
    public array $category_ids = [];

    public string $title = '';

    public string $slug = '';

    public string $short_description = '';

    public string $body = '';

    public bool $is_active = true;

    public bool $is_featured = false;

    public string $published_at = '';

    public string $meta_title = '';

    public string $meta_description = '';

    public bool $is_indexable = true;

    public string $canonical_url = '';

    public bool $slugManuallyEdited = false;

    public bool $metaTitleManuallyEdited = false;

    public bool $metaDescriptionManuallyEdited = false;

    public function mount(Tenant $tenant, CurrentTenant $currentTenant): void
    {
        $this->tenant = $tenant;
        $this->authorize('manageArticles', $tenant);
        $currentTenant->set($tenant);
        $this->published_at = now()->format('Y-m-d\TH:i');
    }

    public function updatedTitle(): void
    {
        if (! $this->slugManuallyEdited) {
            $this->slug = Str::slug($this->title);
        }

        if (! $this->metaTitleManuallyEdited) {
            $this->meta_title = $this->title === '' ? '' : Str::limit($this->title, 60, '');
        }

        if (! $this->metaDescriptionManuallyEdited) {
            $this->meta_description = $this->automaticMetaDescription();
        }
    }

    public function updatedShortDescription(): void
    {
        if (! $this->metaDescriptionManuallyEdited) {
            $this->meta_description = $this->automaticMetaDescription();
        }
    }

    public function updatedSlug(): void
    {
        $this->slugManuallyEdited = $this->slug !== Str::slug($this->title);
    }

    public function updatedMetaTitle(): void
    {
        $this->metaTitleManuallyEdited = $this->meta_title !== ($this->title === '' ? '' : Str::limit($this->title, 60, ''));
    }

    public function updatedMetaDescription(): void
    {
        $this->metaDescriptionManuallyEdited = $this->meta_description !== $this->automaticMetaDescription();
    }

    public function updatedPrimaryCategoryId(?int $value): void
    {
        if ($value === null) {
            return;
        }

        if (! in_array($value, $this->category_ids, true)) {
            $this->category_ids[] = $value;
        }
    }

    public function saveArticle(HtmlSanitizer $sanitizer): void
    {
        $this->authorize('manageArticles', $this->tenant);

        $this->body = $sanitizer->sanitize($this->body);

        $validated = $this->validate($this->rules());
        $categoryIds = $this->resolvedCategoryIds($validated['primary_category_id'], $validated['category_ids'] ?? []);

        $article = $this->editingArticleId === null
            ? new Article
            : $this->article($this->editingArticleId);

        $article->fill([
            'tenant_id' => $this->tenant->id,
            'primary_category_id' => $validated['primary_category_id'],
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'short_description' => $validated['short_description'] ?: null,
            'body' => $validated['body'],
            'is_active' => $validated['is_active'],
            'is_featured' => $validated['is_featured'],
            'published_at' => $validated['published_at'] ?: null,
            'meta_title' => $validated['meta_title'] ?: null,
            'meta_description' => $validated['meta_description'] ?: null,
            'is_indexable' => $validated['is_indexable'],
            'canonical_url' => $validated['canonical_url'] ?: null,
        ]);
        $article->save();
        $article->categories()->sync($categoryIds);

        $this->resetForm();
        $this->dispatch('article-saved');
    }

    public function editArticle(int $articleId): void
    {
        $article = $this->article($articleId);
        $this->authorize('update', $article);

        $this->editingArticleId = $article->id;
        $this->primary_category_id = $article->primary_category_id;
        $this->category_ids = $article->categories()->pluck('categories.id')->map(fn ($id): int => (int) $id)->all();
        $this->title = $article->title;
        $this->slug = $article->slug;
        $this->short_description = $article->short_description ?? '';
        $this->body = $article->body;
        $this->is_active = $article->is_active;
        $this->is_featured = $article->is_featured;
        $this->published_at = $article->published_at?->format('Y-m-d\TH:i') ?? '';
        $this->meta_title = $article->meta_title ?? '';
        $this->meta_description = $article->meta_description ?? '';
        $this->is_indexable = $article->is_indexable;
        $this->canonical_url = $article->canonical_url ?? '';
        $this->slugManuallyEdited = true;
        $this->metaTitleManuallyEdited = true;
        $this->metaDescriptionManuallyEdited = true;
    }

    public function deleteArticle(int $articleId): void
    {
        $article = $this->article($articleId);
        $this->authorize('delete', $article);
        $article->delete();

        if ($this->editingArticleId === $articleId) {
            $this->resetForm();
        }
    }

    public function toggleActive(int $articleId): void
    {
        $this->authorize('manageArticles', $this->tenant);

        $article = $this->article($articleId);
        $article->update(['is_active' => ! $article->is_active]);
    }

    public function toggleFeatured(int $articleId): void
    {
        $this->authorize('manageArticles', $this->tenant);

        $article = $this->article($articleId);
        $article->update(['is_featured' => ! $article->is_featured]);
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    public function render(): View
    {
        return view('livewire.tenants.manage-articles', [
            'articles' => $this->tenant->articles()
                ->with(['primaryCategory', 'categories'])
                ->latest('published_at')
                ->latest('id')
                ->get(),
            'categories' => $this->tenant->categories()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        $tenantCategoryIds = $this->tenant->categories()->pluck('id')->all();

        return [
            'primary_category_id' => [
                'required',
                'integer',
                Rule::in($tenantCategoryIds),
            ],
            'category_ids' => ['array'],
            'category_ids.*' => [
                'integer',
                Rule::in($tenantCategoryIds),
            ],
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('articles', 'slug')
                    ->where('tenant_id', $this->tenant->id)
                    ->ignore($this->editingArticleId),
            ],
            'short_description' => ['nullable', 'string', 'max:500'],
            'body' => ['required', 'string'],
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
            'published_at' => ['nullable', 'date'],
            'meta_title' => ['nullable', 'string', 'max:60'],
            'meta_description' => ['nullable', 'string', 'max:160'],
            'is_indexable' => ['boolean'],
            'canonical_url' => ['nullable', 'url', 'max:2048'],
        ];
    }

    protected function article(int $articleId): Article
    {
        return $this->tenant->articles()->whereKey($articleId)->firstOrFail();
    }

    /**
     * @param  list<int>  $categoryIds
     * @return list<int>
     */
    protected function resolvedCategoryIds(int $primaryCategoryId, array $categoryIds): array
    {
        return collect($categoryIds)
            ->push($primaryCategoryId)
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    protected function automaticMetaDescription(): string
    {
        if ($this->short_description !== '') {
            return Str::limit($this->short_description, 160, '');
        }

        if ($this->title === '') {
            return '';
        }

        return Str::limit($this->title.' haberinin detayları ve güncel gelişmeler.', 160, '');
    }

    protected function resetForm(): void
    {
        $this->reset([
            'editingArticleId',
            'primary_category_id',
            'category_ids',
            'title',
            'slug',
            'short_description',
            'body',
            'meta_title',
            'meta_description',
            'canonical_url',
            'slugManuallyEdited',
            'metaTitleManuallyEdited',
            'metaDescriptionManuallyEdited',
        ]);
        $this->is_active = true;
        $this->is_featured = false;
        $this->is_indexable = true;
        $this->published_at = now()->format('Y-m-d\TH:i');
    }
}

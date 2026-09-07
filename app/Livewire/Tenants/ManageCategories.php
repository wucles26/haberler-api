<?php

namespace App\Livewire\Tenants;

use App\Models\Category;
use App\Models\Tenant;
use App\Support\CurrentTenant;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ManageCategories extends Component
{
    public Tenant $tenant;

    public ?int $editingCategoryId = null;

    public string $name = '';

    public string $slug = '';

    public string $description = '';

    public bool $is_active = true;

    public int $sort_order = 0;

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
        $this->authorize('manageCategories', $tenant);
        $currentTenant->set($tenant);
    }

    public function updatedName(): void
    {
        if (! $this->slugManuallyEdited) {
            $this->slug = Str::slug($this->name);
        }

        if (! $this->metaTitleManuallyEdited) {
            $this->meta_title = $this->name === '' ? '' : $this->name.' Haberleri';
        }

        if (! $this->metaDescriptionManuallyEdited) {
            $this->meta_description = $this->name === ''
                ? ''
                : $this->name.' kategorisindeki son dakika haberleri ve güncel gelişmeler.';
        }
    }

    public function updatedSlug(): void
    {
        $this->slugManuallyEdited = $this->slug !== Str::slug($this->name);
    }

    public function updatedMetaTitle(): void
    {
        $this->metaTitleManuallyEdited = $this->meta_title !== ($this->name === '' ? '' : $this->name.' Haberleri');
    }

    public function updatedMetaDescription(): void
    {
        $automaticDescription = $this->name === ''
            ? ''
            : $this->name.' kategorisindeki son dakika haberleri ve güncel gelişmeler.';

        $this->metaDescriptionManuallyEdited = $this->meta_description !== $automaticDescription;
    }

    public function saveCategory(): void
    {
        $this->authorize('manageCategories', $this->tenant);

        $validated = $this->validate($this->rules());
        $category = $this->editingCategoryId === null
            ? new Category
            : $this->category($this->editingCategoryId);

        $category->fill([
            ...$validated,
            'tenant_id' => $this->tenant->id,
        ]);
        $category->save();

        $this->resetForm();
        $this->dispatch('category-saved');
    }

    public function editCategory(int $categoryId): void
    {
        $category = $this->category($categoryId);
        $this->authorize('update', $category);
        $this->editingCategoryId = $category->id;
        $this->name = $category->name;
        $this->slug = $category->slug;
        $this->description = $category->description ?? '';
        $this->is_active = $category->is_active;
        $this->sort_order = $category->sort_order;
        $this->meta_title = $category->meta_title ?? '';
        $this->meta_description = $category->meta_description ?? '';
        $this->is_indexable = $category->is_indexable;
        $this->canonical_url = $category->canonical_url ?? '';
        $this->slugManuallyEdited = true;
        $this->metaTitleManuallyEdited = true;
        $this->metaDescriptionManuallyEdited = true;
    }

    public function deleteCategory(int $categoryId): void
    {
        $category = $this->category($categoryId);
        $this->authorize('delete', $category);
        $category->delete();

        if ($this->editingCategoryId === $categoryId) {
            $this->resetForm();
        }
    }

    public function toggleActive(int $categoryId): void
    {
        $this->authorize('manageCategories', $this->tenant);

        $category = $this->category($categoryId);
        $category->update(['is_active' => ! $category->is_active]);
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    public function render(): View
    {
        return view('livewire.tenants.manage-categories', [
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
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'slug')
                    ->where('tenant_id', $this->tenant->id)
                    ->ignore($this->editingCategoryId),
            ],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'sort_order' => ['integer', 'min:0'],
            'meta_title' => ['nullable', 'string', 'max:60'],
            'meta_description' => ['nullable', 'string', 'max:160'],
            'is_indexable' => ['boolean'],
            'canonical_url' => ['nullable', 'url', 'max:2048'],
        ];
    }

    protected function category(int $categoryId): Category
    {
        return $this->tenant->categories()->whereKey($categoryId)->firstOrFail();
    }

    protected function resetForm(): void
    {
        $this->reset([
            'editingCategoryId',
            'name',
            'slug',
            'description',
            'sort_order',
            'meta_title',
            'meta_description',
            'canonical_url',
            'slugManuallyEdited',
            'metaTitleManuallyEdited',
            'metaDescriptionManuallyEdited',
        ]);
        $this->is_active = true;
        $this->is_indexable = true;
    }
}

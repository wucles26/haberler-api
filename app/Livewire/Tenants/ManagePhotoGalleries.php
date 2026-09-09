<?php

namespace App\Livewire\Tenants;

use App\Models\GalleryPhoto;
use App\Models\PhotoGallery;
use App\Models\Tenant;
use App\Support\CurrentTenant;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class ManagePhotoGalleries extends Component
{
    use WithFileUploads;

    public Tenant $tenant;

    public ?int $editingGalleryId = null;

    public string $title = '';

    public string $slug = '';

    public string $description = '';

    public bool $is_active = true;

    public int $sort_order = 0;

    public bool $slugManuallyEdited = false;

    public ?TemporaryUploadedFile $cover = null;

    /** @var array<int, TemporaryUploadedFile> */
    public array $photos = [];

    /** @var array<int, int> */
    public array $photoOrders = [];

    public function mount(Tenant $tenant, CurrentTenant $currentTenant): void
    {
        $this->tenant = $tenant;
        $this->authorize('managePhotoGalleries', $tenant);
        $currentTenant->set($tenant);
    }

    public function updatedTitle(): void
    {
        if (! $this->slugManuallyEdited) {
            $this->slug = Str::slug($this->title);
        }
    }

    public function updatedSlug(): void
    {
        $this->slugManuallyEdited = $this->slug !== Str::slug($this->title);
    }

    public function saveGallery(): void
    {
        $this->authorize('managePhotoGalleries', $this->tenant);
        $validated = $this->validate($this->rules());
        $gallery = $this->editingGalleryId === null ? new PhotoGallery : $this->gallery($this->editingGalleryId);
        $oldCover = $gallery->cover_path;
        $gallery->fill([...$validated, 'tenant_id' => $this->tenant->id]);

        if ($this->cover !== null) {
            $gallery->cover_path = $this->cover->store('photo-galleries/covers', 'public');
        }

        $gallery->save();

        if ($this->cover !== null && $oldCover !== null) {
            Storage::disk('public')->delete($oldCover);
        }

        foreach ($this->photos as $photo) {
            $gallery->photos()->create([
                'path' => $photo->store('photo-galleries/photos', 'public'),
                'original_name' => $photo->getClientOriginalName(),
                'sort_order' => $gallery->photos()->count(),
                'is_active' => true,
            ]);
        }

        $this->resetForm();
        $this->dispatch('gallery-saved');
    }

    public function editGallery(int $galleryId): void
    {
        $gallery = $this->gallery($galleryId);
        $this->authorize('update', $gallery);
        $this->editingGalleryId = $gallery->id;
        $this->title = $gallery->title;
        $this->slug = $gallery->slug;
        $this->description = $gallery->description ?? '';
        $this->is_active = $gallery->is_active;
        $this->sort_order = $gallery->sort_order;
        $this->slugManuallyEdited = true;
    }

    public function deleteGallery(int $galleryId): void
    {
        $gallery = $this->gallery($galleryId);
        $this->authorize('delete', $gallery);
        Storage::disk('public')->delete(array_filter([$gallery->cover_path, ...$gallery->photos->pluck('path')->all()]));
        $gallery->delete();
        $this->resetForm();
    }

    public function toggleGalleryActive(int $galleryId): void
    {
        $this->authorize('managePhotoGalleries', $this->tenant);
        $gallery = $this->gallery($galleryId);
        $gallery->update(['is_active' => ! $gallery->is_active]);
    }

    public function togglePhotoActive(int $photoId): void
    {
        $photo = GalleryPhoto::query()->whereHas('gallery', fn ($query) => $query->where('tenant_id', $this->tenant->id))->findOrFail($photoId);
        $this->authorize('update', $photo);
        $photo->update(['is_active' => ! $photo->is_active]);
    }

    public function updatePhotoOrder(int $photoId, int $sortOrder): void
    {
        $photo = GalleryPhoto::query()->whereHas('gallery', fn ($query) => $query->where('tenant_id', $this->tenant->id))->findOrFail($photoId);
        $this->authorize('update', $photo);
        $validated = validator(['sort_order' => $sortOrder], ['sort_order' => ['integer', 'min:0']])->validate();
        $photo->update($validated);
    }

    public function deletePhoto(int $photoId): void
    {
        $photo = GalleryPhoto::query()->whereHas('gallery', fn ($query) => $query->where('tenant_id', $this->tenant->id))->findOrFail($photoId);
        $this->authorize('delete', $photo);
        Storage::disk('public')->delete($photo->path);
        $photo->delete();
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    public function render(): View
    {
        $galleries = $this->tenant->photoGalleries()->with('photos')->orderBy('sort_order')->orderBy('id')->get();
        $this->photoOrders = $galleries->flatMap(fn (PhotoGallery $gallery): array => $gallery->photos->mapWithKeys(fn (GalleryPhoto $photo): array => [$photo->id => $photo->sort_order])->all())->all();

        return view('livewire.tenants.manage-photo-galleries', [
            'galleries' => $galleries,
        ]);
    }

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('photo_galleries', 'slug')->where('tenant_id', $this->tenant->id)->ignore($this->editingGalleryId)],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'sort_order' => ['integer', 'min:0'],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'photos' => ['array', 'max:50'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    protected function gallery(int $galleryId): PhotoGallery
    {
        return $this->tenant->photoGalleries()->whereKey($galleryId)->firstOrFail();
    }

    protected function resetForm(): void
    {
        $this->reset(['editingGalleryId', 'title', 'slug', 'description', 'sort_order', 'slugManuallyEdited', 'cover', 'photos', 'photoOrders']);
        $this->is_active = true;
    }
}

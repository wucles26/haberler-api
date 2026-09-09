<?php

namespace App\Livewire\Tenants;

use App\Models\Tenant;
use App\Models\Video;
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
class ManageVideos extends Component
{
    use WithFileUploads;

    public Tenant $tenant;

    public ?int $editingVideoId = null;

    public string $title = '';

    public string $slug = '';

    public string $video_url = '';

    public string $embed_code = '';

    public bool $is_active = true;

    public int $sort_order = 0;

    public bool $slugManuallyEdited = false;

    public ?TemporaryUploadedFile $thumbnail = null;

    public function mount(Tenant $tenant, CurrentTenant $currentTenant): void
    {
        $this->tenant = $tenant;
        $this->authorize('manageVideos', $tenant);
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

    public function updatedVideoUrl(): void
    {
        $this->embed_code = $this->embedCodeFor($this->video_url) ?? '';
    }

    public function saveVideo(): void
    {
        $this->authorize('manageVideos', $this->tenant);
        $validated = $this->validate($this->rules());
        $embedCode = $this->embedCodeFor($validated['video_url']);
        abort_unless($embedCode !== null, 422);
        $video = $this->editingVideoId === null ? new Video : $this->video($this->editingVideoId);
        $oldThumbnail = $video->thumbnail_path;
        $video->fill([
            ...$validated,
            'tenant_id' => $this->tenant->id,
            'embed_code' => $embedCode,
        ]);

        if ($this->thumbnail !== null) {
            $video->thumbnail_path = $this->thumbnail->store('videos/thumbnails', 'public');
        }

        $video->save();

        if ($this->thumbnail !== null && $oldThumbnail !== null) {
            Storage::disk('public')->delete($oldThumbnail);
        }

        $this->resetForm();
        $this->dispatch('video-saved');
    }

    public function editVideo(int $videoId): void
    {
        $video = $this->video($videoId);
        $this->authorize('update', $video);
        $this->editingVideoId = $video->id;
        $this->title = $video->title;
        $this->slug = $video->slug;
        $this->video_url = $video->video_url;
        $this->embed_code = $video->embed_code;
        $this->is_active = $video->is_active;
        $this->sort_order = $video->sort_order;
        $this->slugManuallyEdited = true;
    }

    public function deleteVideo(int $videoId): void
    {
        $video = $this->video($videoId);
        $this->authorize('delete', $video);
        Storage::disk('public')->delete($video->thumbnail_path);
        $video->delete();
        $this->resetForm();
    }

    public function toggleActive(int $videoId): void
    {
        $this->authorize('manageVideos', $this->tenant);
        $video = $this->video($videoId);
        $video->update(['is_active' => ! $video->is_active]);
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    public function render(): View
    {
        return view('livewire.tenants.manage-videos', [
            'videos' => $this->tenant->videos()->orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('videos', 'slug')->where('tenant_id', $this->tenant->id)->ignore($this->editingVideoId)],
            'video_url' => ['required', 'url', 'max:2048'],
            'is_active' => ['boolean'],
            'sort_order' => ['integer', 'min:0'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    protected function video(int $videoId): Video
    {
        return $this->tenant->videos()->whereKey($videoId)->firstOrFail();
    }

    protected function embedCodeFor(string $url): ?string
    {
        $host = parse_url($url, PHP_URL_HOST);
        $path = trim((string) parse_url($url, PHP_URL_PATH), '/');
        $query = (string) parse_url($url, PHP_URL_QUERY);
        $videoId = null;

        if (in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtu.be', 'www.youtu.be'], true)) {
            parse_str($query, $parameters);
            $videoId = $host === 'youtu.be' || $host === 'www.youtu.be'
                ? explode('/', $path)[0]
                : ($parameters['v'] ?? (str_starts_with($path, 'embed/') ? Str::after($path, 'embed/') : null));
            $embedUrl = 'https://www.youtube.com/embed/'.($videoId ?? '');
        } elseif (in_array($host, ['vimeo.com', 'www.vimeo.com'], true)) {
            $videoId = preg_match('/^\d+$/', $path) === 1 ? $path : null;
            $embedUrl = 'https://player.vimeo.com/video/'.($videoId ?? '');
        } else {
            return null;
        }

        if ($videoId === null || preg_match('/^[A-Za-z0-9_-]+$/', $videoId) !== 1) {
            return null;
        }

        return '<iframe src="'.e($embedUrl).'" title="'.e($this->title).'" allowfullscreen></iframe>';
    }

    protected function resetForm(): void
    {
        $this->reset(['editingVideoId', 'title', 'slug', 'video_url', 'embed_code', 'sort_order', 'slugManuallyEdited', 'thumbnail']);
        $this->is_active = true;
    }
}

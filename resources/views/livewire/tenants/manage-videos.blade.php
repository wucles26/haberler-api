<div>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4"><div><h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Video galleries') }}</h2><p class="mt-1 text-sm text-gray-500">{{ $tenant->name }}</p></div><a href="{{ route('tenant.dashboard', $tenant) }}" wire:navigate class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">{{ __('Back to dashboard') }}</a></div>
    </x-slot>
    <div class="py-12"><div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="bg-white shadow-sm sm:rounded-lg"><div class="p-6 text-gray-900"><h3 class="text-lg font-medium">{{ $editingVideoId === null ? __('Add video') : __('Edit video') }}</h3>
            <form wire:submit="saveVideo" enctype="multipart/form-data" class="mt-4 grid gap-4 sm:grid-cols-2">
                <div><x-input-label for="title" :value="__('Title')" /><x-text-input wire:model.live="title" id="title" class="block mt-1 w-full" required /><x-input-error :messages="$errors->get('title')" class="mt-2" /></div>
                <div><x-input-label for="slug" :value="__('Slug')" /><x-text-input wire:model.live="slug" id="slug" class="block mt-1 w-full" required /><x-input-error :messages="$errors->get('slug')" class="mt-2" /></div>
                <div class="sm:col-span-2"><x-input-label for="video_url" :value="__('YouTube or Vimeo URL')" /><x-text-input wire:model.live="video_url" id="video_url" type="url" class="block mt-1 w-full" required /><x-input-error :messages="$errors->get('video_url')" class="mt-2" /></div>
                <div><x-input-label for="thumbnail" :value="__('Thumbnail')" /><input wire:model="thumbnail" id="thumbnail" type="file" accept="image/*" class="block mt-1 w-full" /><x-input-error :messages="$errors->get('thumbnail')" class="mt-2" /></div>
                <div><x-input-label for="sort_order" :value="__('Sort order')" /><x-text-input wire:model="sort_order" id="sort_order" type="number" min="0" class="block mt-1 w-full" /><x-input-error :messages="$errors->get('sort_order')" class="mt-2" /></div>
                <label class="inline-flex items-center gap-2"><input wire:model="is_active" type="checkbox" class="rounded border-gray-300 text-indigo-600"><span class="text-sm text-gray-700">{{ __('Active') }}</span></label>
                @if ($embed_code !== '')<div class="sm:col-span-2 rounded border border-gray-200 p-3 text-xs text-gray-500">{{ __('Embed code will be generated automatically.') }}</div>@endif
                <div class="sm:col-span-2 flex gap-3"><x-primary-button>{{ $editingVideoId === null ? __('Save video') : __('Update video') }}</x-primary-button>@if ($editingVideoId !== null)<x-secondary-button type="button" wire:click="cancelEdit">{{ __('Cancel') }}</x-secondary-button>@endif</div>
            </form>
        </div></div>
        <div class="bg-white shadow-sm sm:rounded-lg"><div class="p-6 text-gray-900"><h3 class="text-lg font-medium">{{ __('Videos') }}</h3><div class="mt-4 space-y-3">
            @forelse ($videos as $video)<div wire:key="video-{{ $video->id }}" class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 pb-3"><div><div class="font-medium">{{ $video->title }}</div><div class="text-xs text-gray-500">{{ $video->video_url }}</div></div><div class="flex gap-4 text-sm"><button type="button" wire:click="toggleActive({{ $video->id }})" class="{{ $video->is_active ? 'text-green-600' : 'text-gray-500' }}">{{ $video->is_active ? __('Active') : __('Inactive') }}</button><button type="button" wire:click="editVideo({{ $video->id }})" class="text-indigo-600">{{ __('Edit') }}</button><button type="button" wire:click="deleteVideo({{ $video->id }})" wire:confirm="{{ __('Delete this video?') }}" class="text-red-600">{{ __('Delete') }}</button></div></div>@empty<p class="text-gray-500">{{ __('No videos yet.') }}</p>@endforelse
        </div></div></div>
    </div></div>
</div>

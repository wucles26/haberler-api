<div>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Photo galleries') }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ $tenant->name }}</p>
            </div>
            <a href="{{ route('tenant.dashboard', $tenant) }}" wire:navigate class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">{{ __('Back to dashboard') }}</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium">{{ $editingGalleryId === null ? __('Add photo gallery') : __('Edit photo gallery') }}</h3>
                    <form wire:submit="saveGallery" enctype="multipart/form-data" class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div><x-input-label for="title" :value="__('Title')" /><x-text-input wire:model.live="title" id="title" class="block mt-1 w-full" required /><x-input-error :messages="$errors->get('title')" class="mt-2" /></div>
                        <div><x-input-label for="slug" :value="__('Slug')" /><x-text-input wire:model.live="slug" id="slug" class="block mt-1 w-full" required /><x-input-error :messages="$errors->get('slug')" class="mt-2" /></div>
                        <div class="sm:col-span-2"><x-input-label for="description" :value="__('Description')" /><textarea wire:model="description" id="description" rows="3" class="border-gray-300 rounded-md shadow-sm block mt-1 w-full"></textarea><x-input-error :messages="$errors->get('description')" class="mt-2" /></div>
                        <div><x-input-label for="cover" :value="__('Cover photo')" /><input wire:model="cover" id="cover" type="file" accept="image/*" class="block mt-1 w-full" /><x-input-error :messages="$errors->get('cover')" class="mt-2" /></div>
                        <div><x-input-label for="photos" :value="__('Gallery photos')" /><input wire:model="photos" id="photos" type="file" accept="image/*" multiple class="block mt-1 w-full" /><x-input-error :messages="$errors->get('photos')" class="mt-2" /></div>
                        <div><x-input-label for="sort_order" :value="__('Sort order')" /><x-text-input wire:model="sort_order" id="sort_order" type="number" min="0" class="block mt-1 w-full" /><x-input-error :messages="$errors->get('sort_order')" class="mt-2" /></div>
                        <label class="inline-flex items-center gap-2 sm:pt-7"><input wire:model="is_active" type="checkbox" class="rounded border-gray-300 text-indigo-600"><span class="text-sm text-gray-700">{{ __('Active') }}</span></label>
                        <div class="sm:col-span-2 flex gap-3"><x-primary-button>{{ $editingGalleryId === null ? __('Save gallery') : __('Update gallery') }}</x-primary-button>@if ($editingGalleryId !== null)<x-secondary-button type="button" wire:click="cancelEdit">{{ __('Cancel') }}</x-secondary-button>@endif</div>
                    </form>
                </div>
            </div>
            <div class="bg-white shadow-sm sm:rounded-lg"><div class="p-6 text-gray-900"><h3 class="text-lg font-medium">{{ __('Photo galleries') }}</h3><div class="mt-4 space-y-6">
                @forelse ($galleries as $gallery)
                    <div wire:key="gallery-{{ $gallery->id }}" class="border-b border-gray-100 pb-5">
                        <div class="flex flex-wrap items-center justify-between gap-3"><div><div class="font-medium">{{ $gallery->title }}</div><div class="text-xs text-gray-500">{{ $gallery->slug }} · {{ $gallery->photos->count() }} {{ __('photos') }}</div></div><div class="flex gap-4 text-sm"><button type="button" wire:click="toggleGalleryActive({{ $gallery->id }})" class="{{ $gallery->is_active ? 'text-green-600' : 'text-gray-500' }}">{{ $gallery->is_active ? __('Active') : __('Inactive') }}</button><button type="button" wire:click="editGallery({{ $gallery->id }})" class="text-indigo-600">{{ __('Edit') }}</button><button type="button" wire:click="deleteGallery({{ $gallery->id }})" wire:confirm="{{ __('Delete this gallery?') }}" class="text-red-600">{{ __('Delete') }}</button></div></div>
                        <div class="mt-3 flex flex-wrap gap-3">@foreach ($gallery->photos as $photo)<div wire:key="photo-{{ $photo->id }}" class="w-28"><img src="{{ Storage::disk('public')->url($photo->path) }}" alt="{{ $photo->original_name }}" class="h-20 w-28 rounded object-cover"><x-text-input wire:model.blur="photoOrders.{{ $photo->id }}" wire:change="updatePhotoOrder({{ $photo->id }}, $event.target.value)" type="number" min="0" class="mt-1 w-full text-xs" /><div class="mt-1 flex justify-between text-xs"><button type="button" wire:click="togglePhotoActive({{ $photo->id }})" class="{{ $photo->is_active ? 'text-green-600' : 'text-gray-500' }}">{{ $photo->is_active ? __('On') : __('Off') }}</button><button type="button" wire:click="deletePhoto({{ $photo->id }})" wire:confirm="{{ __('Delete this photo?') }}" class="text-red-600">{{ __('Delete') }}</button></div></div>@endforeach</div>
                    </div>
                @empty
                    <p class="text-gray-500">{{ __('No photo galleries yet.') }}</p>
                @endforelse
            </div></div></div>
        </div>
    </div>
</div>

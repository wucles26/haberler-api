<div>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Categories') }}
                </h2>
                <p class="mt-1 text-sm text-gray-500">{{ $tenant->name }}</p>
            </div>

            <div class="flex items-center gap-3">
                @can('manageArticles', $tenant)
                    <a
                        href="{{ route('tenant.articles', $tenant) }}"
                        wire:navigate
                        class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700"
                    >
                        {{ __('Articles') }}
                    </a>
                @endcan

                <a
                    href="{{ route('tenant.dashboard', $tenant) }}"
                    wire:navigate
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50"
                >
                    {{ __('Back to dashboard') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium">
                        {{ $editingCategoryId === null ? __('Add category') : __('Edit category') }}
                    </h3>

                    <form wire:submit="saveCategory" class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div>
                            <x-input-label for="name" :value="__('Category name')" />
                            <x-text-input wire:model.live="name" id="name" class="block mt-1 w-full" required />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="slug" :value="__('Slug')" />
                            <x-text-input wire:model.live="slug" id="slug" class="block mt-1 w-full" required />
                            <x-input-error :messages="$errors->get('slug')" class="mt-2" />
                        </div>

                        <div class="sm:col-span-2">
                            <x-input-label for="description" :value="__('Description')" />
                            <textarea wire:model="description" id="description" rows="3" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"></textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="sort_order" :value="__('Sort order')" />
                            <x-text-input wire:model="sort_order" id="sort_order" class="block mt-1 w-full" type="number" min="0" />
                            <x-input-error :messages="$errors->get('sort_order')" class="mt-2" />
                        </div>

                        <div class="flex items-center gap-6 sm:pt-7">
                            <label class="inline-flex items-center gap-2">
                                <input wire:model="is_active" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="text-sm text-gray-700">{{ __('Active') }}</span>
                            </label>
                            <label class="inline-flex items-center gap-2">
                                <input wire:model="is_indexable" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="text-sm text-gray-700">{{ __('Indexable') }}</span>
                            </label>
                        </div>

                        <div>
                            <x-input-label for="meta_title" :value="__('SEO title')" />
                            <x-text-input wire:model.live="meta_title" id="meta_title" class="block mt-1 w-full" maxlength="60" />
                            <p class="mt-1 text-xs text-gray-500">{{ strlen($meta_title) }}/60</p>
                            <x-input-error :messages="$errors->get('meta_title')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="canonical_url" :value="__('Canonical URL')" />
                            <x-text-input wire:model="canonical_url" id="canonical_url" class="block mt-1 w-full" type="url" />
                            <x-input-error :messages="$errors->get('canonical_url')" class="mt-2" />
                        </div>

                        <div class="sm:col-span-2">
                            <x-input-label for="meta_description" :value="__('SEO description')" />
                            <textarea wire:model.live="meta_description" id="meta_description" rows="3" maxlength="160" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"></textarea>
                            <p class="mt-1 text-xs text-gray-500">{{ strlen($meta_description) }}/160</p>
                            <x-input-error :messages="$errors->get('meta_description')" class="mt-2" />
                        </div>

                        <div class="sm:col-span-2 flex gap-3">
                            <x-primary-button>
                                {{ $editingCategoryId === null ? __('Save category') : __('Update category') }}
                            </x-primary-button>

                            @if ($editingCategoryId !== null)
                                <x-secondary-button type="button" wire:click="cancelEdit">
                                    {{ __('Cancel') }}
                                </x-secondary-button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium">{{ __('Categories') }}</h3>

                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead>
                                <tr class="text-left text-gray-500">
                                    <th class="py-2 pr-4 font-medium">{{ __('Name') }}</th>
                                    <th class="py-2 pr-4 font-medium">{{ __('Slug') }}</th>
                                    <th class="py-2 pr-4 font-medium">{{ __('Status') }}</th>
                                    <th class="py-2 font-medium">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($categories as $category)
                                    <tr wire:key="category-{{ $category->id }}">
                                        <td class="py-3 pr-4">{{ $category->name }}</td>
                                        <td class="py-3 pr-4">{{ $category->slug }}</td>
                                        <td class="py-3 pr-4">
                                            <button type="button" wire:click="toggleActive({{ $category->id }})" class="{{ $category->is_active ? 'text-green-600' : 'text-gray-500' }}">
                                                {{ $category->is_active ? __('Active') : __('Inactive') }}
                                            </button>
                                        </td>
                                        <td class="py-3 flex gap-4">
                                            <button type="button" wire:click="editCategory({{ $category->id }})" class="text-indigo-600 hover:text-indigo-800">
                                                {{ __('Edit') }}
                                            </button>
                            @can('delete', $category)
                                                <button type="button" wire:click="deleteCategory({{ $category->id }})" wire:confirm="{{ __('Delete this category?') }}" class="text-red-600 hover:text-red-800">
                                                    {{ __('Delete') }}
                                                </button>
                                            @endcan
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

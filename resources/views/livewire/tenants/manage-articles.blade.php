<div>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Articles') }}
                </h2>
                <p class="mt-1 text-sm text-gray-500">{{ $tenant->name }}</p>
            </div>

            <a
                href="{{ route('tenant.dashboard', $tenant) }}"
                wire:navigate
                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50"
            >
                {{ __('Back to dashboard') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium">
                        {{ $editingArticleId === null ? __('Add article') : __('Edit article') }}
                    </h3>

                    <form wire:submit="saveArticle" class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div>
                            <x-input-label for="primary_category_id" :value="__('Primary category')" />
                            <select wire:model.live="primary_category_id" id="primary_category_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full" required>
                                <option value="">{{ __('Select a category') }}</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('primary_category_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="category_ids" :value="__('Additional categories')" />
                            <select wire:model="category_ids" id="category_ids" multiple class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full min-h-28">
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">{{ __('Primary category is always included.') }}</p>
                            <x-input-error :messages="$errors->get('category_ids')" class="mt-2" />
                            @foreach ($errors->getMessages() as $field => $messages)
                                @if (str_starts_with($field, 'category_ids.'))
                                    <x-input-error :messages="$messages" class="mt-2" />
                                @endif
                            @endforeach
                        </div>

                        <div>
                            <x-input-label for="title" :value="__('Title')" />
                            <x-text-input wire:model.live="title" id="title" class="block mt-1 w-full" required />
                            <x-input-error :messages="$errors->get('title')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="slug" :value="__('Slug')" />
                            <x-text-input wire:model.live="slug" id="slug" class="block mt-1 w-full" required />
                            <x-input-error :messages="$errors->get('slug')" class="mt-2" />
                        </div>

                        <div class="sm:col-span-2">
                            <x-input-label for="short_description" :value="__('Short description')" />
                            <textarea wire:model.live="short_description" id="short_description" rows="2" maxlength="500" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"></textarea>
                            <x-input-error :messages="$errors->get('short_description')" class="mt-2" />
                        </div>

                        <div class="sm:col-span-2">
                            <x-input-label for="body" :value="__('Article body')" />
                            <div
                                wire:ignore
                                x-data="{
                                    sync() {
                                        $wire.set('body', $refs.editor.innerHTML)
                                    },
                                    format(command, value = null) {
                                        document.execCommand(command, false, value)
                                        this.sync()
                                    },
                                    link() {
                                        const url = prompt('URL')
                                        if (url) {
                                            document.execCommand('createLink', false, url)
                                            this.sync()
                                        }
                                    }
                                }"
                            >
                                <div class="mt-1 flex flex-wrap gap-2">
                                    <button type="button" class="rounded border border-gray-300 px-2 py-1 text-xs font-semibold text-gray-700 hover:bg-gray-50" @click="format('bold')">{{ __('Bold') }}</button>
                                    <button type="button" class="rounded border border-gray-300 px-2 py-1 text-xs font-semibold text-gray-700 hover:bg-gray-50" @click="format('italic')">{{ __('Italic') }}</button>
                                    <button type="button" class="rounded border border-gray-300 px-2 py-1 text-xs font-semibold text-gray-700 hover:bg-gray-50" @click="format('underline')">{{ __('Underline') }}</button>
                                    <button type="button" class="rounded border border-gray-300 px-2 py-1 text-xs font-semibold text-gray-700 hover:bg-gray-50" @click="format('insertUnorderedList')">{{ __('List') }}</button>
                                    <button type="button" class="rounded border border-gray-300 px-2 py-1 text-xs font-semibold text-gray-700 hover:bg-gray-50" @click="format('formatBlock', 'h2')">{{ __('Heading') }}</button>
                                    <button type="button" class="rounded border border-gray-300 px-2 py-1 text-xs font-semibold text-gray-700 hover:bg-gray-50" @click="link()">{{ __('Link') }}</button>
                                </div>
                                <div
                                    x-ref="editor"
                                    id="body"
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-2 min-h-40 w-full border bg-white p-3"
                                    contenteditable="true"
                                    x-on:input="sync()"
                                    x-on:blur="sync()"
                                    x-init="$refs.editor.innerHTML = $wire.body || ''; $watch('$wire.body', value => { if ($refs.editor.innerHTML !== value) { $refs.editor.innerHTML = value || '' } })"
                                ></div>
                            </div>
                            <x-input-error :messages="$errors->get('body')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="published_at" :value="__('Published at')" />
                            <x-text-input wire:model="published_at" id="published_at" class="block mt-1 w-full" type="datetime-local" />
                            <x-input-error :messages="$errors->get('published_at')" class="mt-2" />
                        </div>

                        <div class="flex flex-wrap items-center gap-6 sm:pt-7">
                            <label class="inline-flex items-center gap-2">
                                <input wire:model="is_active" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="text-sm text-gray-700">{{ __('Active') }}</span>
                            </label>
                            <label class="inline-flex items-center gap-2">
                                <input wire:model="is_featured" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="text-sm text-gray-700">{{ __('Featured') }}</span>
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
                                {{ $editingArticleId === null ? __('Save article') : __('Update article') }}
                            </x-primary-button>

                            @if ($editingArticleId !== null)
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
                    <h3 class="text-lg font-medium">{{ __('Articles') }}</h3>

                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead>
                                <tr class="text-left text-gray-500">
                                    <th class="py-2 pr-4 font-medium">{{ __('Title') }}</th>
                                    <th class="py-2 pr-4 font-medium">{{ __('Primary category') }}</th>
                                    <th class="py-2 pr-4 font-medium">{{ __('Status') }}</th>
                                    <th class="py-2 pr-4 font-medium">{{ __('Featured') }}</th>
                                    <th class="py-2 font-medium">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($articles as $article)
                                    <tr wire:key="article-{{ $article->id }}">
                                        <td class="py-3 pr-4">
                                            <div>{{ $article->title }}</div>
                                            <div class="text-xs text-gray-500">{{ $article->slug }}</div>
                                        </td>
                                        <td class="py-3 pr-4">
                                            <div>{{ $article->primaryCategory?->name }}</div>
                                            @if ($article->categories->count() > 1)
                                                <div class="text-xs text-gray-500">
                                                    {{ $article->categories->pluck('name')->join(', ') }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="py-3 pr-4">
                                            <button type="button" wire:click="toggleActive({{ $article->id }})" class="{{ $article->is_active ? 'text-green-600' : 'text-gray-500' }}">
                                                {{ $article->is_active ? __('Active') : __('Inactive') }}
                                            </button>
                                        </td>
                                        <td class="py-3 pr-4">
                                            <button type="button" wire:click="toggleFeatured({{ $article->id }})" class="{{ $article->is_featured ? 'text-amber-600' : 'text-gray-500' }}">
                                                {{ $article->is_featured ? __('Featured') : __('Not featured') }}
                                            </button>
                                        </td>
                                        <td class="py-3 flex gap-4">
                                            <button type="button" wire:click="editArticle({{ $article->id }})" class="text-indigo-600 hover:text-indigo-800">
                                                {{ __('Edit') }}
                                            </button>
                                            @can('delete', $article)
                                                <button type="button" wire:click="deleteArticle({{ $article->id }})" wire:confirm="{{ __('Delete this article?') }}" class="text-red-600 hover:text-red-800">
                                                    {{ __('Delete') }}
                                                </button>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-4 text-gray-500">{{ __('No articles yet.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

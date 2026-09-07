<div>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Members') }}
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $tenant->name }}
                </p>
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
                    <h3 class="text-lg font-medium">{{ __('Add member') }}</h3>

                    <form wire:submit="addMember" class="mt-4 grid gap-4 sm:grid-cols-3">
                        <div class="sm:col-span-2">
                            <x-input-label for="email" :value="__('Email')" />
                            <x-text-input wire:model="email" id="email" class="block mt-1 w-full" type="email" required />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="role" :value="__('Role')" />
                            <select
                                wire:model="role"
                                id="role"
                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"
                            >
                                @foreach ($roles as $availableRole)
                                    <option value="{{ $availableRole->value }}">{{ $availableRole->label() }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('role')" class="mt-2" />
                        </div>

                        <div class="sm:col-span-3">
                            <x-primary-button>
                                {{ __('Add member') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium">{{ __('Current members') }}</h3>
                    <x-input-error :messages="$errors->get('members')" class="mt-2" />

                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead>
                                <tr class="text-left text-gray-500">
                                    <th class="py-2 pr-4 font-medium">{{ __('Name') }}</th>
                                    <th class="py-2 pr-4 font-medium">{{ __('Email') }}</th>
                                    <th class="py-2 pr-4 font-medium">{{ __('Role') }}</th>
                                    <th class="py-2 font-medium">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($memberships as $membership)
                                    <tr wire:key="membership-{{ $membership->id }}">
                                        <td class="py-3 pr-4">{{ $membership->user->name }}</td>
                                        <td class="py-3 pr-4">{{ $membership->user->email }}</td>
                                        <td class="py-3 pr-4">
                                            <select
                                                wire:change="updateRole({{ $membership->id }}, $event.target.value)"
                                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                            >
                                                @foreach ($roles as $availableRole)
                                                    <option
                                                        value="{{ $availableRole->value }}"
                                                        @selected($membership->role === $availableRole)
                                                    >
                                                        {{ $availableRole->label() }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="py-3">
                                            <button
                                                type="button"
                                                wire:click="removeMember({{ $membership->id }})"
                                                wire:confirm="{{ __('Remove this member from the publication?') }}"
                                                class="text-red-600 hover:text-red-800"
                                            >
                                                {{ __('Remove') }}
                                            </button>
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

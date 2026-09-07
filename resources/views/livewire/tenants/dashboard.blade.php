<div>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Dashboard') }}
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $tenant->name }}
                </p>
            </div>

            @can('manageMembers', $tenant)
                <a
                    href="{{ route('tenant.members', $tenant) }}"
                    wire:navigate
                    class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700"
                >
                    {{ __('Manage members') }}
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-4">
                    <p>{{ __('Welcome to your publication dashboard.') }}</p>

                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div class="rounded-lg border border-gray-200 p-4">
                            <dt class="text-sm text-gray-500">{{ __('Publication') }}</dt>
                            <dd class="mt-1 text-lg font-semibold">{{ $tenant->name }}</dd>
                        </div>

                        <div class="rounded-lg border border-gray-200 p-4">
                            <dt class="text-sm text-gray-500">{{ __('Your role') }}</dt>
                            <dd class="mt-1 text-lg font-semibold">{{ $membership?->role->label() }}</dd>
                        </div>

                        <div class="rounded-lg border border-gray-200 p-4">
                            <dt class="text-sm text-gray-500">{{ __('Members') }}</dt>
                            <dd class="mt-1 text-lg font-semibold">{{ $memberCount }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>

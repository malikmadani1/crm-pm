<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Settings" description="Manage system-level workspace options and support tools." />
    </x-slot>

    <div class="space-y-6">
        <div class="panel">
            <h2 class="text-lg font-semibold text-white">{{ __('Settings') }}</h2>
            <p class="mt-2 text-sm leading-7 text-slate-400">
                {{ __('No additional workspace settings are configured yet.') }}
            </p>
        </div>
    </div>
</x-app-layout>

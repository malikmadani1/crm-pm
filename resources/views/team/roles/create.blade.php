<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Create Role" description="Bundle permissions into reusable access profiles." />
    </x-slot>

    <form method="POST" action="{{ route('roles.store') }}" class="space-y-6">
        @csrf
        @include('team.roles._form')

        <div class="flex justify-end gap-3">
            <a href="{{ route('roles.index') }}" class="btn-secondary">{{ __('Cancel') }}</a>
            <button class="btn-primary">{{ __('Create Role') }}</button>
        </div>
    </form>
</x-app-layout>

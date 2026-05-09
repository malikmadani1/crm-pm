<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Create Project" description="Start a new delivery workspace with budget, ownership, and members." />
    </x-slot>

    <form method="POST" action="{{ route('projects.store') }}" class="space-y-6">
        @csrf
        @include('pm.projects._form')

        <div class="flex justify-end gap-3">
            <a href="{{ route('projects.index') }}" class="btn-secondary">{{ __('Cancel') }}</a>
            <button class="btn-primary">{{ __('Save Project') }}</button>
        </div>
    </form>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Create Task" description="Add a new execution item into the project workflow." />
    </x-slot>

    <form method="POST" action="{{ route('tasks.store') }}" class="space-y-6">
        @csrf
        @include('pm.tasks._form')

        <div class="flex justify-end gap-3">
            <a href="{{ route('tasks.index') }}" class="btn-secondary">{{ __('Cancel') }}</a>
            <button class="btn-primary">{{ __('Save Task') }}</button>
        </div>
    </form>
</x-app-layout>

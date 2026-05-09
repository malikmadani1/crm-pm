<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Log Time" description="Capture billable and operational effort against projects and tasks." />
    </x-slot>

    <form method="POST" action="{{ route('time-entries.store') }}" class="space-y-6">
        @csrf
        @include('pm.time-entries._form')

        <div class="flex justify-end gap-3">
            <a href="{{ route('time-entries.index') }}" class="btn-secondary">{{ __('Cancel') }}</a>
            <button class="btn-primary">{{ __('Save Entry') }}</button>
        </div>
    </form>
</x-app-layout>

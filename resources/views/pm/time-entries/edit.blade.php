<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Edit Time Entry" description="Adjust logged duration and allocation details." />
    </x-slot>

    <form method="POST" action="{{ route('time-entries.update', $timeEntry) }}" class="space-y-6">
        @csrf
        @method('PUT')
        @include('pm.time-entries._form', ['timeEntry' => $timeEntry])

        <div class="flex justify-end gap-3">
            <a href="{{ route('time-entries.show', $timeEntry) }}" class="btn-secondary">{{ __('Back') }}</a>
            <button class="btn-primary">{{ __('Save Changes') }}</button>
        </div>
    </form>
</x-app-layout>

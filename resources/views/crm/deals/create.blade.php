<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Create Deal" description="Capture an active commercial opportunity." />
    </x-slot>

    <form method="POST" action="{{ route('deals.store') }}" class="space-y-6">
        @csrf
        @include('crm.deals._form')

        <div class="flex justify-end gap-3">
            <a href="{{ route('deals.index') }}" class="btn-secondary">{{ __('Cancel') }}</a>
            <button class="btn-primary">{{ __('Save Deal') }}</button>
        </div>
    </form>
</x-app-layout>

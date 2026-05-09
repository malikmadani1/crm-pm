<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Create Lead" description="Capture a new opportunity into your CRM pipeline." />
    </x-slot>

    <form method="POST" action="{{ route('leads.store') }}" class="space-y-6">
        @csrf
        @include('crm.leads._form')

        <div class="flex justify-end gap-3">
            <a href="{{ route('leads.index') }}" class="btn-secondary">{{ __('Cancel') }}</a>
            <button class="btn-primary">{{ __('Save Lead') }}</button>
        </div>
    </form>
</x-app-layout>

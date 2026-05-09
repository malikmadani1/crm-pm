<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Create Customer" description="Start a complete customer profile with ownership and relationship data." />
    </x-slot>

    <form method="POST" action="{{ route('customers.store') }}" class="space-y-6">
        @csrf
        @include('crm.customers._form')

        <div class="flex justify-end gap-3">
            <a href="{{ route('customers.index') }}" class="btn-secondary">{{ __('Cancel') }}</a>
            <button class="btn-primary">{{ __('Save Customer') }}</button>
        </div>
    </form>
</x-app-layout>

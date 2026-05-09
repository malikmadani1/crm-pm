<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Edit Customer" description="Update customer information, ownership, and account notes." />
    </x-slot>

    <form method="POST" action="{{ route('customers.update', $customer) }}" class="space-y-6">
        @csrf
        @method('PUT')
        @include('crm.customers._form', ['customer' => $customer])

        <div class="flex justify-end gap-3">
            <a href="{{ route('customers.show', $customer) }}" class="btn-secondary">{{ __('Back') }}</a>
            <button class="btn-primary">{{ __('Save Changes') }}</button>
        </div>
    </form>
</x-app-layout>

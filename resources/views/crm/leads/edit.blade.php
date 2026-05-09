<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Edit Lead" description="Update stage, ownership, and qualification data." />
    </x-slot>

    <form method="POST" action="{{ route('leads.update', $lead) }}" class="space-y-6">
        @csrf
        @method('PUT')
        @include('crm.leads._form', ['lead' => $lead])

        <div class="flex justify-end gap-3">
            <a href="{{ route('leads.show', $lead) }}" class="btn-secondary">{{ __('Back') }}</a>
            <button class="btn-primary">{{ __('Save Changes') }}</button>
        </div>
    </form>
</x-app-layout>

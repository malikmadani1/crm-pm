<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Edit Deal" description="Adjust stage, ownership, and commercial expectations." />
    </x-slot>

    <form method="POST" action="{{ route('deals.update', $deal) }}" class="space-y-6">
        @csrf
        @method('PUT')
        @include('crm.deals._form', ['deal' => $deal])

        <div class="flex justify-end gap-3">
            <a href="{{ route('deals.show', $deal) }}" class="btn-secondary">{{ __('Back') }}</a>
            <button class="btn-primary">{{ __('Save Changes') }}</button>
        </div>
    </form>
</x-app-layout>

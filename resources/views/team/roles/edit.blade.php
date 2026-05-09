<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Edit Role" description="Adjust the access scope granted by this role." />
    </x-slot>

    <form method="POST" action="{{ route('roles.update', $role) }}" class="space-y-6">
        @csrf
        @method('PUT')
        @include('team.roles._form', ['role' => $role])

        <div class="flex justify-end gap-3">
            <a href="{{ route('roles.show', $role) }}" class="btn-secondary">{{ __('Back') }}</a>
            <button class="btn-primary">{{ __('Save Changes') }}</button>
        </div>
    </form>
</x-app-layout>

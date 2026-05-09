<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Edit User" description="Update profile information, permissions, and account status." />
    </x-slot>

    <form method="POST" action="{{ route('users.update', $user) }}" class="space-y-6">
        @csrf
        @method('PUT')
        @include('team.users._form', ['user' => $user])

        <div class="flex justify-end gap-3">
            <a href="{{ route('users.show', $user) }}" class="btn-secondary">{{ __('Back') }}</a>
            <button class="btn-primary">{{ __('Save Changes') }}</button>
        </div>
    </form>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Create User" description="Invite a new team member with the right role and access profile." />
    </x-slot>

    <form method="POST" action="{{ route('users.store') }}" class="space-y-6">
        @csrf
        @include('team.users._form')

        <div class="flex justify-end gap-3">
            <a href="{{ route('users.index') }}" class="btn-secondary">{{ __('Cancel') }}</a>
            <button class="btn-primary">{{ __('Create User') }}</button>
        </div>
    </form>
</x-app-layout>

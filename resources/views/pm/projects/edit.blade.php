<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Edit Project" description="Update execution details, team members, and customer linkage." />
    </x-slot>

    <form method="POST" action="{{ route('projects.update', $project) }}" class="space-y-6">
        @csrf
        @method('PUT')
        @include('pm.projects._form', ['project' => $project])

        <div class="flex justify-end gap-3">
            <a href="{{ route('projects.show', $project) }}" class="btn-secondary">{{ __('Back') }}</a>
            <button class="btn-primary">{{ __('Save Changes') }}</button>
        </div>
    </form>
</x-app-layout>

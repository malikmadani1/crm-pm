<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Edit Task" description="Adjust assignees, workload, status, and structure." />
    </x-slot>

    <form method="POST" action="{{ route('tasks.update', $task) }}" class="space-y-6">
        @csrf
        @method('PUT')
        @include('pm.tasks._form', ['task' => $task])

        <div class="flex justify-end gap-3">
            <a href="{{ route('tasks.show', $task) }}" class="btn-secondary">{{ __('Back') }}</a>
            <button class="btn-primary">{{ __('Save Changes') }}</button>
        </div>
    </form>
</x-app-layout>

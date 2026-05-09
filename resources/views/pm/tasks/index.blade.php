<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Tasks" description="Operational execution across projects with ownership, status, priority, and deadlines.">
            <a href="{{ route('tasks.create') }}" class="btn-primary">{{ __('Create Task') }}</a>
        </x-page-header>
    </x-slot>

    <div class="space-y-6">
        <form class="panel grid gap-4 lg:grid-cols-5">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search tasks') }}">

            <select name="project_id">
                <option value="">{{ __('All projects') }}</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}" @selected((string) request('project_id') === (string) $project->id)>{{ $project->name }}</option>
                @endforeach
            </select>

            <select name="status">
                <option value="">{{ __('All statuses') }}</option>
                @foreach(\App\Models\Task::STATUSES as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ __(str($status)->replace('_', ' ')->title()->toString()) }}</option>
                @endforeach
            </select>

            <select name="priority">
                <option value="">{{ __('All priorities') }}</option>
                @foreach(\App\Models\Task::PRIORITIES as $priority)
                    <option value="{{ $priority }}" @selected(request('priority') === $priority)>{{ __(str($priority)->title()->toString()) }}</option>
                @endforeach
            </select>

            <button class="btn-secondary">{{ __('Apply Filters') }}</button>
        </form>

        @if($tasks->count())
            <div class="table-shell">
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('Task') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Priority') }}</th>
                            <th>{{ __('Due') }}</th>
                            <th class="text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tasks as $task)
                            <tr>
                                <td>
                                    <div class="font-semibold text-white">{{ $task->title }}</div>
                                    <div class="text-xs text-slate-400">{{ $task->project?->name }} | {{ $task->assignees->pluck('name')->join(', ') ?: __('Unassigned') }}</div>
                                </td>
                                <td><x-status-badge :value="$task->status" :color="config('crm_pm.labels.task_statuses.' . $task->status . '.color', 'slate')" /></td>
                                <td><x-status-badge :value="$task->priority" :color="config('crm_pm.labels.priorities.' . $task->priority . '.color', 'slate')" /></td>
                                <td>{{ optional($task->due_date)->format('Y-m-d') ?: __('N/A') }}</td>
                                <td class="text-right">
                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                        <a href="{{ route('tasks.show', $task) }}" class="rounded-xl border border-cyan-400/20 bg-cyan-500/10 px-3 py-2 text-xs font-semibold text-cyan-300 transition hover:bg-cyan-500/20">
                                            {{ __('Open') }}
                                        </a>
                                        @can('update', $task)
                                            <a href="{{ route('tasks.edit', $task) }}" class="rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-xs font-semibold text-slate-200 transition hover:bg-white/10">
                                                {{ __('Edit') }}
                                            </a>
                                        @endcan
                                        @can('delete', $task)
                                            <x-delete-action
                                                :action="route('tasks.destroy', $task)"
                                                :title="__('Delete task')"
                                                :message="__('Are you sure you want to delete this task?')"
                                            />
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $tasks->links() }}
        @else
            <x-empty-state title="No tasks yet" message="Tasks are the heartbeat of delivery. Create one to start tracking execution." action="Create Task" :href="route('tasks.create')" />
        @endif
    </div>
</x-app-layout>

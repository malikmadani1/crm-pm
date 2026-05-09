<x-app-layout>
    <x-slot name="header">
        <x-page-header :title="$project->name" :description="$project->description ?: 'Project overview, progress, tasks, and time tracking.'">
            @can('update', $project)
                <a href="{{ route('projects.edit', $project) }}" class="btn-secondary">{{ __('Edit') }}</a>
            @endcan
            @can('delete', $project)
                <x-delete-action
                    :action="route('projects.destroy', $project)"
                    :title="__('Delete project')"
                    :message="__('Are you sure you want to delete this project?')"
                />
            @endcan
            @can('create', \App\Models\Task::class)
                <a href="{{ route('tasks.create', ['project_id' => $project->id]) }}" class="btn-primary">{{ __('Add Task') }}</a>
            @endcan
        </x-page-header>
    </x-slot>

    <div class="grid gap-6 xl:grid-cols-[0.7fr_1.3fr]">
        <div class="space-y-6">
            <div class="panel">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm text-slate-400">{{ $project->customer?->name ?: __('Internal project') }}</div>
                        <div class="mt-1 text-3xl font-semibold text-white">{{ $project->progress }}%</div>
                    </div>
                    <x-status-badge :value="$project->status" :color="config('crm_pm.labels.project_statuses.' . $project->status . '.color', 'slate')" />
                </div>

                <div class="mt-4 h-3 overflow-hidden rounded-full bg-slate-800">
                    <div class="h-full rounded-full bg-cyan-400" style="width: {{ $project->progress }}%"></div>
                </div>

                <dl class="mt-6 space-y-3 text-sm">
                    <div class="flex justify-between"><dt class="text-slate-400">{{ __('Manager') }}</dt><dd>{{ $project->manager?->name ?: __('N/A') }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-400">{{ __('Budget') }}</dt><dd>${{ number_format($project->budget, 2) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-400">{{ __('Due Date') }}</dt><dd>{{ optional($project->due_date)->format('Y-m-d') ?: __('N/A') }}</dd></div>
                </dl>
            </div>

            <div class="panel">
                <h3 class="text-lg font-semibold text-white">{{ __('Team Members') }}</h3>

                <div class="mt-4 space-y-3">
                    @forelse($project->members as $member)
                        <a href="{{ route('users.show', $member) }}" class="block rounded-2xl bg-white/5 px-4 py-4 hover:bg-white/10">
                            <div class="font-semibold text-white">{{ $member->name }}</div>
                            <div class="mt-1 text-xs text-slate-400">{{ __($member->pivot->role) }}</div>
                        </a>
                    @empty
                        <div class="text-sm text-slate-400">{{ __('No team members assigned.') }}</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="panel">
                <h3 class="text-lg font-semibold text-white">{{ __('Recent Tasks') }}</h3>

                <div class="mt-4 space-y-3">
                    @forelse($recentTasks as $task)
                        <a href="{{ route('tasks.show', $task) }}" class="block rounded-2xl bg-white/5 px-4 py-4 hover:bg-white/10">
                            <div class="flex items-center justify-between">
                                <div class="font-semibold text-white">{{ $task->title }}</div>
                                <x-status-badge :value="$task->status" :color="config('crm_pm.labels.task_statuses.' . $task->status . '.color', 'slate')" />
                            </div>
                            <div class="mt-1 text-xs text-slate-400">{{ $task->assignees->pluck('name')->join(', ') ?: __('Unassigned') }}</div>
                        </a>
                    @empty
                        <div class="text-sm text-slate-400">{{ __('No tasks yet.') }}</div>
                    @endforelse
                </div>
            </div>

            <div class="panel">
                <h3 class="text-lg font-semibold text-white">{{ __('Time Tracking') }}</h3>

                <div class="mt-4 space-y-3">
                    @forelse($project->timeEntries as $entry)
                        <div class="rounded-2xl bg-white/5 px-4 py-4">
                            <div class="flex items-center justify-between">
                                <div class="font-semibold text-white">{{ $entry->user?->name }}</div>
                                <div class="text-xs text-slate-400">{{ round($entry->minutes / 60, 1) }} {{ __('hours') }}</div>
                            </div>
                            <div class="mt-1 text-xs text-slate-400">{{ $entry->description ?: __('No description') }}</div>
                        </div>
                    @empty
                        <div class="text-sm text-slate-400">{{ __('No time entries recorded.') }}</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

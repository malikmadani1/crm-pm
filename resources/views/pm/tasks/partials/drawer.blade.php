<div class="task-drawer-shell flex h-full flex-col">
    <div class="task-drawer-header border-b border-white/10 px-5 py-5">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0 flex-1">
                <div class="text-xs font-semibold tracking-[0.2em] text-slate-500">{{ __('Task') }}</div>
                <h2 class="mt-2 text-xl font-semibold text-white">{{ $task->title }}</h2>
                <div class="mt-2 text-sm text-slate-400">{{ $task->project?->name ?: __('No linked project') }}</div>
            </div>

            <button
                type="button"
                class="rounded-2xl border border-white/10 bg-white/5 px-3 py-2 text-slate-300 transition hover:bg-white/10"
                data-task-panel-close
                aria-label="{{ __('Close') }}"
            >
                &times;
            </button>
        </div>

        <div class="mt-4 flex flex-wrap gap-2">
            <x-status-badge :value="$task->status" :color="config('crm_pm.labels.task_statuses.' . $task->status . '.color', 'slate')" />
            <x-status-badge :value="$task->priority" :color="config('crm_pm.labels.priorities.' . $task->priority . '.color', 'slate')" />
            <x-status-badge :value="$task->completion_percentage . '%'" color="sky" />
        </div>

        <div class="mt-5">
            @include('pm.tasks.partials.status-switcher', ['task' => $task, 'compact' => true])
        </div>

        <div class="mt-5 flex flex-wrap gap-3">
            <a href="{{ route('tasks.show', $task) }}" class="btn-primary">{{ __('Open full view') }}</a>
            @can('update', $task)
                <a href="{{ route('tasks.edit', $task) }}" class="btn-secondary">{{ __('Edit') }}</a>
            @endcan
            @can('delete', $task)
                <x-delete-action
                    :action="route('tasks.destroy', $task)"
                    :title="__('Delete task')"
                    :message="__('Are you sure you want to delete this task?')"
                />
            @endcan
        </div>
    </div>

    <div class="task-drawer-body flex-1 space-y-4 overflow-y-auto px-5 py-5">
        @include('pm.tasks.partials.timer-card', [
            'task' => $task,
            'activeTimer' => $activeTimer,
            'taskTrackedMinutes' => $taskTrackedMinutes,
            'taskUserTrackedMinutes' => $taskUserTrackedMinutes,
            'compact' => true,
        ])

        <div class="task-drawer-section rounded-3xl border border-white/10 bg-white/5 p-4">
            <div class="mb-4 flex items-center justify-between">
                <div class="text-sm font-semibold text-white">{{ __('Progress') }}</div>
                <div class="text-sm text-slate-300">{{ $task->completion_percentage }}%</div>
            </div>
            <div class="h-3 overflow-hidden rounded-full bg-slate-800">
                <div class="h-full rounded-full bg-cyan-400" style="width: {{ $task->completion_percentage }}%"></div>
            </div>

            <dl class="mt-5 space-y-3 text-sm">
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-400">{{ __('Due Date') }}</dt>
                    <dd class="text-right text-slate-200">{{ optional($task->due_date)->format('Y-m-d') ?: __('Not specified') }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-400">{{ __('Estimated Hours') }}</dt>
                    <dd class="text-right text-slate-200">{{ \App\Support\Duration::fromHours($task->estimated_hours ?: 0) }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-400">{{ __('Actual Hours') }}</dt>
                    <dd class="text-right text-slate-200">{{ \App\Support\Duration::fromHours($task->actual_hours ?: 0) }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-400">{{ __('Created by') }}</dt>
                    <dd class="text-right text-slate-200">{{ $task->creator?->name ?: __('System') }}</dd>
                </div>
            </dl>
        </div>

        <div class="task-drawer-section rounded-3xl border border-white/10 bg-white/5 p-4">
            <div class="text-sm font-semibold text-white">{{ __('Description') }}</div>
            <div class="mt-3 text-sm leading-7 text-slate-300">{{ $task->description ?: __('No task description yet.') }}</div>
        </div>

        <div class="task-drawer-section rounded-3xl border border-white/10 bg-white/5 p-4">
            <div class="text-sm font-semibold text-white">{{ __('Assignees') }}</div>
            <div class="mt-3 flex flex-wrap gap-2">
                @forelse($task->assignees as $assignee)
                    <x-status-badge :value="$assignee->name" color="cyan" />
                @empty
                    <span class="text-sm text-slate-400">{{ __('No assignees yet.') }}</span>
                @endforelse
            </div>
        </div>

        <div class="task-drawer-section rounded-3xl border border-white/10 bg-white/5 p-4">
            <div class="text-sm font-semibold text-white">{{ __('Tags') }}</div>
            <div class="mt-3 flex flex-wrap gap-2">
                @forelse($task->tags as $tag)
                    <x-status-badge :value="$tag->name" color="amber" />
                @empty
                    <span class="text-sm text-slate-400">{{ __('No tags.') }}</span>
                @endforelse
            </div>
        </div>

        <div class="task-drawer-section rounded-3xl border border-white/10 bg-white/5 p-4">
            <div class="mb-3 flex items-center justify-between">
                <div class="text-sm font-semibold text-white">{{ __('Latest updates') }}</div>
                <div class="text-xs text-slate-500">{{ __(':count updates', ['count' => $task->logs->count()]) }}</div>
            </div>

            <div class="space-y-3">
                @forelse($task->logs->take(5) as $log)
                    <div class="rounded-2xl bg-slate-950/50 px-4 py-3">
                        <div class="text-sm font-medium text-white">{{ $log->titleLabel() }}</div>
                        <div class="mt-2 space-y-1 text-xs leading-6 text-slate-400">
                            @foreach($log->detailLines() as $line)
                                <div>{{ $line }}</div>
                            @endforeach
                        </div>
                        <div class="mt-1 text-[11px] text-slate-500">{{ $log->created_at?->diffForHumans() }}</div>
                    </div>
                @empty
                    <div class="text-sm text-slate-400">{{ __('No activity recorded yet.') }}</div>
                @endforelse
            </div>
        </div>
    </div>
</div>



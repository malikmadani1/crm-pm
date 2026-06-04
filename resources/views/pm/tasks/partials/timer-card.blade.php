@php
    $compact = $compact ?? false;
    $currentUserTimerOnTask = $activeTimer && (int) $activeTimer->task_id === (int) $task->id;
    $anotherTimerRunning = $activeTimer && ! $currentUserTimerOnTask;
@endphp

<div class="task-drawer-section rounded-3xl border border-white/10 bg-white/5 p-4">
    <div class="flex items-start justify-between gap-4">
        <div>
            <div class="text-sm font-semibold text-white">{{ __('Task timer') }}</div>
            <div class="mt-1 text-xs text-slate-400">
                @if($currentUserTimerOnTask)
                    {{ __('The timer is currently running on this task.') }}
                @elseif($anotherTimerRunning)
                    {{ __('You have a timer running on another task: :task', ['task' => $activeTimer->task?->title ?? __('Another task')]) }}
                @else
                    {{ __('Start time when you begin working on this task.') }}
                @endif
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            @if($currentUserTimerOnTask)
                <button
                    type="button"
                    class="btn-secondary"
                    data-task-timer-action="stop"
                    data-task-timer-url="{{ route('tasks.timer.stop', $task) }}"
                >
                    {{ __('Stop time') }}
                </button>
            @else
                <button
                    type="button"
                    class="btn-primary"
                    data-task-timer-action="start"
                    data-task-timer-url="{{ route('tasks.timer.start', $task) }}"
                    @disabled($anotherTimerRunning)
                >
                    {{ __('Start time') }}
                </button>
            @endif
        </div>
    </div>

    <div class="mt-4 grid gap-3 {{ $compact ? 'grid-cols-2' : 'md:grid-cols-3' }}">
        <div class="rounded-2xl bg-slate-950/40 px-4 py-3">
            <div class="text-xs text-slate-400">{{ __('Total task time') }}</div>
            <div class="mt-1 text-lg font-semibold text-white">{{ \App\Support\Duration::fromMinutes($taskTrackedMinutes) }}</div>
        </div>
        <div class="rounded-2xl bg-slate-950/40 px-4 py-3">
            <div class="text-xs text-slate-400">{{ __('Your time on this task') }}</div>
            <div class="mt-1 text-lg font-semibold text-white">{{ \App\Support\Duration::fromMinutes($taskUserTrackedMinutes) }}</div>
        </div>
        <div class="rounded-2xl bg-slate-950/40 px-4 py-3 {{ $compact ? 'col-span-2' : '' }}">
            <div class="text-xs text-slate-400">{{ __('Current status') }}</div>
            <div class="mt-1 text-sm font-semibold text-white">
                @if($currentUserTimerOnTask)
                    {{ __('Running since :time', ['time' => $activeTimer->started_at?->diffForHumans()]) }}
                @elseif($anotherTimerRunning)
                    {{ __('You have an open timer on another task') }}
                @else
                    {{ __('No timer is running now') }}
                @endif
            </div>
        </div>
    </div>

    <div class="mt-4">
        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">{{ __('Work note') }}</label>
        <textarea
            rows="{{ $compact ? 3 : 4 }}"
            class="task-timer-note"
            data-task-timer-description
            placeholder="{{ __('Briefly write what you are working on or what you completed in this session...') }}"
        ></textarea>
    </div>

    @if($task->timeEntries->isNotEmpty())
        <div class="mt-4 space-y-2">
            <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">{{ __('Latest sessions') }}</div>
            @foreach($task->timeEntries->sortByDesc('started_at')->take($compact ? 3 : 5) as $entry)
                <div class="flex items-center justify-between rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm">
                    <div>
                        <div class="font-medium text-white">{{ $entry->user?->name ?? __('System') }}</div>
                        <div class="mt-1 text-xs text-slate-400">
                            {{ $entry->started_at?->format('Y-m-d H:i') }}
                            @if($entry->ended_at)
                                - {{ $entry->ended_at?->format('H:i') }}
                            @else
                                - {{ __('Running now') }}
                            @endif
                        </div>
                        @if($entry->description)
                            <div class="mt-2 text-xs text-slate-400">{{ $entry->description }}</div>
                        @endif
                    </div>
                    <div class="text-sm font-semibold text-cyan-300">
                        {{ $entry->ended_at ? $entry->durationLabel() : __('Running') }}
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>



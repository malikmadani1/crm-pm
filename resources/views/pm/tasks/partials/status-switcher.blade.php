@php
    $compact = $compact ?? false;
    $statusLabels = collect(\App\Models\Task::STATUSES)
        ->mapWithKeys(fn ($status) => [$status => \App\Support\Labels::status($status)])
        ->all();
@endphp

<div class="task-status-switcher {{ $compact ? 'task-status-switcher-compact' : '' }}">
    <div class="task-status-switcher-title">{{ $compact ? __('Change status') : __('Quick status change') }}</div>
    <div class="task-status-switcher-grid">
        @foreach(\App\Models\Task::STATUSES as $status)
            <button
                type="button"
                class="task-status-chip {{ $task->status === $status ? 'is-active' : '' }}"
                data-task-status-button
                data-task-id="{{ $task->id }}"
                data-task-status="{{ $status }}"
                data-task-url="{{ route('tasks.show', $task) }}"
                data-task-move-url="{{ route('kanban.move', $task) }}"
            >
                {{ $statusLabels[$status] ?? $status }}
            </button>
        @endforeach
    </div>
</div>

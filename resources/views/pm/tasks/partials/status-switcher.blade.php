@php
    $compact = $compact ?? false;
    $statusLabels = [
        'todo' => 'للعمل',
        'in_progress' => 'قيد التنفيذ',
        'review' => 'قيد المراجعة',
        'done' => 'مكتملة',
    ];
@endphp

<div class="task-status-switcher {{ $compact ? 'task-status-switcher-compact' : '' }}">
    <div class="task-status-switcher-title">{{ $compact ? 'تغيير الحالة' : 'تغيير الحالة بسرعة' }}</div>
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

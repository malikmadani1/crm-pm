@php
    $compact = $compact ?? false;
    $currentUserTimerOnTask = $activeTimer && (int) $activeTimer->task_id === (int) $task->id;
    $anotherTimerRunning = $activeTimer && ! $currentUserTimerOnTask;
@endphp

<div class="task-drawer-section rounded-3xl border border-white/10 bg-white/5 p-4">
    <div class="flex items-start justify-between gap-4">
        <div>
            <div class="text-sm font-semibold text-white">مؤقت المهمة</div>
            <div class="mt-1 text-xs text-slate-400">
                @if($currentUserTimerOnTask)
                    المؤقت يعمل الآن على هذه المهمة.
                @elseif($anotherTimerRunning)
                    لديك مؤقت يعمل على مهمة أخرى: {{ $activeTimer->task?->title ?? 'مهمة أخرى' }}
                @else
                    ابدأ الوقت عند البدء بالعمل على هذه المهمة.
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
                    إيقاف الوقت
                </button>
            @else
                <button
                    type="button"
                    class="btn-primary"
                    data-task-timer-action="start"
                    data-task-timer-url="{{ route('tasks.timer.start', $task) }}"
                    @disabled($anotherTimerRunning)
                >
                    ابدأ الوقت
                </button>
            @endif
        </div>
    </div>

    <div class="mt-4 grid gap-3 {{ $compact ? 'grid-cols-2' : 'md:grid-cols-3' }}">
        <div class="rounded-2xl bg-slate-950/40 px-4 py-3">
            <div class="text-xs text-slate-400">إجمالي وقت المهمة</div>
            <div class="mt-1 text-lg font-semibold text-white">{{ round($taskTrackedMinutes / 60, 2) }} ساعة</div>
        </div>
        <div class="rounded-2xl bg-slate-950/40 px-4 py-3">
            <div class="text-xs text-slate-400">وقتك على المهمة</div>
            <div class="mt-1 text-lg font-semibold text-white">{{ round($taskUserTrackedMinutes / 60, 2) }} ساعة</div>
        </div>
        <div class="rounded-2xl bg-slate-950/40 px-4 py-3 {{ $compact ? 'col-span-2' : '' }}">
            <div class="text-xs text-slate-400">الحالة الحالية</div>
            <div class="mt-1 text-sm font-semibold text-white">
                @if($currentUserTimerOnTask)
                    يعمل منذ {{ $activeTimer->started_at?->diffForHumans() }}
                @elseif($anotherTimerRunning)
                    لديك مؤقت مفتوح على مهمة أخرى
                @else
                    لا يوجد مؤقت يعمل الآن
                @endif
            </div>
        </div>
    </div>

    @if($task->timeEntries->isNotEmpty())
        <div class="mt-4 space-y-2">
            <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">أحدث الجلسات</div>
            @foreach($task->timeEntries->sortByDesc('started_at')->take($compact ? 3 : 5) as $entry)
                <div class="flex items-center justify-between rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm">
                    <div>
                        <div class="font-medium text-white">{{ $entry->user?->name ?? 'النظام' }}</div>
                        <div class="mt-1 text-xs text-slate-400">
                            {{ $entry->started_at?->format('Y-m-d H:i') }}
                            @if($entry->ended_at)
                                - {{ $entry->ended_at?->format('H:i') }}
                            @else
                                - يعمل الآن
                            @endif
                        </div>
                    </div>
                    <div class="text-sm font-semibold text-cyan-300">
                        {{ $entry->ended_at ? round($entry->minutes / 60, 2).' ساعة' : 'قيد التشغيل' }}
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

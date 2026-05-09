<div class="task-drawer-shell flex h-full flex-col">
    <div class="task-drawer-header border-b border-white/10 px-5 py-5">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0 flex-1">
                <div class="text-xs font-semibold tracking-[0.2em] text-slate-500">المهمة</div>
                <h2 class="mt-2 text-xl font-semibold text-white">{{ $task->title }}</h2>
                <div class="mt-2 text-sm text-slate-400">{{ $task->project?->name ?: 'بدون مشروع مرتبط' }}</div>
            </div>

            <button
                type="button"
                class="rounded-2xl border border-white/10 bg-white/5 px-3 py-2 text-slate-300 transition hover:bg-white/10"
                data-task-panel-close
                aria-label="إغلاق"
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
            <a href="{{ route('tasks.show', $task) }}" class="btn-primary">فتح كامل</a>
            @can('update', $task)
                <a href="{{ route('tasks.edit', $task) }}" class="btn-secondary">تعديل</a>
            @endcan
            @can('delete', $task)
                <x-delete-action
                    :action="route('tasks.destroy', $task)"
                    title="حذف المهمة"
                    message="هل أنت متأكد من حذف هذه المهمة؟"
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
                <div class="text-sm font-semibold text-white">التقدم</div>
                <div class="text-sm text-slate-300">{{ $task->completion_percentage }}%</div>
            </div>
            <div class="h-3 overflow-hidden rounded-full bg-slate-800">
                <div class="h-full rounded-full bg-cyan-400" style="width: {{ $task->completion_percentage }}%"></div>
            </div>

            <dl class="mt-5 space-y-3 text-sm">
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-400">تاريخ الاستحقاق</dt>
                    <dd class="text-right text-slate-200">{{ optional($task->due_date)->format('Y-m-d') ?: 'غير محدد' }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-400">الساعات المقدرة</dt>
                    <dd class="text-right text-slate-200">{{ $task->estimated_hours ?: 0 }} ساعة</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-400">الساعات الفعلية</dt>
                    <dd class="text-right text-slate-200">{{ $task->actual_hours ?: 0 }} ساعة</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-400">أنشأها</dt>
                    <dd class="text-right text-slate-200">{{ $task->creator?->name ?: 'النظام' }}</dd>
                </div>
            </dl>
        </div>

        <div class="task-drawer-section rounded-3xl border border-white/10 bg-white/5 p-4">
            <div class="text-sm font-semibold text-white">الوصف</div>
            <div class="mt-3 text-sm leading-7 text-slate-300">{{ $task->description ?: 'لا يوجد وصف للمهمة بعد.' }}</div>
        </div>

        <div class="task-drawer-section rounded-3xl border border-white/10 bg-white/5 p-4">
            <div class="text-sm font-semibold text-white">المكلّفون</div>
            <div class="mt-3 flex flex-wrap gap-2">
                @forelse($task->assignees as $assignee)
                    <x-status-badge :value="$assignee->name" color="cyan" />
                @empty
                    <span class="text-sm text-slate-400">لا يوجد مكلّفون بعد.</span>
                @endforelse
            </div>
        </div>

        <div class="task-drawer-section rounded-3xl border border-white/10 bg-white/5 p-4">
            <div class="text-sm font-semibold text-white">الوسوم</div>
            <div class="mt-3 flex flex-wrap gap-2">
                @forelse($task->tags as $tag)
                    <x-status-badge :value="$tag->name" color="amber" />
                @empty
                    <span class="text-sm text-slate-400">لا توجد وسوم.</span>
                @endforelse
            </div>
        </div>

        <div class="task-drawer-section rounded-3xl border border-white/10 bg-white/5 p-4">
            <div class="mb-3 flex items-center justify-between">
                <div class="text-sm font-semibold text-white">آخر التحديثات</div>
                <div class="text-xs text-slate-500">{{ $task->logs->count() }} تحديث</div>
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
                    <div class="text-sm text-slate-400">لا يوجد نشاط مسجل بعد.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<x-app-layout>
    <x-slot name="header">
        <x-page-header :title="$task->title" :description="$task->project?->name ?: 'تفاصيل المهمة وسجل العمل عليها'">
            @include('pm.tasks.partials.status-switcher', ['task' => $task, 'compact' => false])
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
        </x-page-header>
    </x-slot>

    <div class="grid gap-6 xl:grid-cols-[0.8fr_1.2fr]">
        <div class="space-y-6">
            <div class="panel">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm text-slate-400">{{ $task->project?->name ?: 'بدون مشروع' }}</div>
                        <div class="mt-1 text-2xl font-semibold text-white">{{ $task->completion_percentage }}%</div>
                    </div>
                    <div class="flex gap-2">
                        <x-status-badge :value="$task->status" :color="config('crm_pm.labels.task_statuses.' . $task->status . '.color', 'slate')" />
                        <x-status-badge :value="$task->priority" :color="config('crm_pm.labels.priorities.' . $task->priority . '.color', 'slate')" />
                    </div>
                </div>

                <div class="mt-4 h-3 overflow-hidden rounded-full bg-slate-800">
                    <div class="h-full rounded-full bg-cyan-400" style="width: {{ $task->completion_percentage }}%"></div>
                </div>

                <dl class="mt-6 space-y-3 text-sm">
                    <div class="flex justify-between"><dt class="text-slate-400">تاريخ الاستحقاق</dt><dd>{{ optional($task->due_date)->format('Y-m-d') ?: 'غير محدد' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-400">الساعات المقدرة</dt><dd>{{ \App\Support\Duration::fromHours($task->estimated_hours ?: 0) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-400">الساعات الفعلية</dt><dd>{{ \App\Support\Duration::fromHours($task->actual_hours ?: 0) }}</dd></div>
                </dl>

                <div class="mt-6 rounded-2xl bg-white/5 p-4 text-sm text-slate-300">{{ $task->description ?: 'لا يوجد وصف للمهمة بعد.' }}</div>
            </div>

            @include('pm.tasks.partials.timer-card', [
                'task' => $task,
                'activeTimer' => $activeTimer,
                'taskTrackedMinutes' => $taskTrackedMinutes,
                'taskUserTrackedMinutes' => $taskUserTrackedMinutes,
                'compact' => false,
            ])

            <div class="panel">
                <h3 class="text-lg font-semibold text-white">المكلّفون والوسوم</h3>

                <div class="mt-4 space-y-4">
                    <div>
                        <div class="mb-2 text-xs uppercase tracking-[0.3em] text-slate-500">المكلّفون</div>
                        <div class="flex flex-wrap gap-2">
                            @forelse($task->assignees as $assignee)
                                <x-status-badge :value="$assignee->name" color="cyan" />
                            @empty
                                <span class="text-sm text-slate-400">لا يوجد مكلّفون بعد.</span>
                            @endforelse
                        </div>
                    </div>

                    <div>
                        <div class="mb-2 text-xs uppercase tracking-[0.3em] text-slate-500">الوسوم</div>
                        <div class="flex flex-wrap gap-2">
                            @forelse($task->tags as $tag)
                                <x-status-badge :value="$tag->name" color="amber" />
                            @empty
                                <span class="text-sm text-slate-400">لا توجد وسوم.</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="panel">
                <h3 class="text-lg font-semibold text-white">التعليقات</h3>

                <form method="POST" action="{{ route('tasks.comments.store', $task) }}" class="mt-4 space-y-4">
                    @csrf
                    <textarea name="body" rows="4" placeholder="اكتب تحديثًا أو ملاحظة تخص المهمة..."></textarea>
                    <button class="btn-primary">إضافة تعليق</button>
                </form>

                <div class="mt-5 space-y-3">
                    @forelse($task->comments as $comment)
                        <div id="comment-{{ $comment->id }}" class="rounded-2xl bg-white/5 px-4 py-4">
                            <div class="flex items-center justify-between">
                                <div class="font-semibold text-white">{{ $comment->user?->name ?: 'النظام' }}</div>
                                <div class="text-xs text-slate-500">{{ $comment->created_at?->diffForHumans() }}</div>
                            </div>
                            <div class="mt-2 text-sm text-slate-300">{{ $comment->body }}</div>
                        </div>
                    @empty
                        <div class="text-sm text-slate-400">لا توجد تعليقات بعد.</div>
                    @endforelse
                </div>
            </div>

            <div class="panel">
                <h3 class="text-lg font-semibold text-white">سجل النشاط</h3>

                <div class="mt-4 space-y-3">
                    @forelse($task->logs as $log)
                        <div class="rounded-2xl bg-white/5 px-4 py-4">
                            <div class="font-semibold text-white">{{ $log->titleLabel() }}</div>
                            <div class="mt-2 space-y-1 text-xs text-slate-400">
                                @foreach($log->detailLines() as $line)
                                    <div>{{ $line }}</div>
                                @endforeach
                            </div>
                            <div class="mt-2 text-[11px] text-slate-500">{{ $log->created_at?->diffForHumans() }}</div>
                        </div>
                    @empty
                        <div class="text-sm text-slate-400">لا يوجد نشاط مسجل بعد.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

            document.querySelectorAll('[data-task-status-button]').forEach((button) => {
                button.addEventListener('click', async () => {
                    const status = button.dataset.taskStatus;
                    const moveUrl = button.dataset.taskMoveUrl;
                    button.disabled = true;

                    try {
                        const response = await fetch(moveUrl, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrf,
                            },
                            body: JSON.stringify({ status }),
                        });

                        if (!response.ok) {
                            throw new Error();
                        }

                        window.location.reload();
                    } catch (error) {
                        window.dispatchEvent(new CustomEvent('app-toast', {
                            detail: { type: 'error', message: 'تعذر تحديث حالة المهمة الآن.' },
                        }));
                    } finally {
                        button.disabled = false;
                    }
                });
            });

            document.querySelectorAll('[data-task-timer-action]').forEach((button) => {
                button.addEventListener('click', async () => {
                    const url = button.dataset.taskTimerUrl;
                    const description = button.closest('.task-drawer-section')?.querySelector('[data-task-timer-description]')?.value?.trim() || '';
                    button.disabled = true;

                    try {
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrf,
                            },
                            body: JSON.stringify({ description }),
                        });
                        const payload = await response.json();

                        if (!response.ok || !payload.success) {
                            throw new Error(payload.message || 'تعذر تنفيذ العملية.');
                        }

                        window.dispatchEvent(new CustomEvent('app-toast', {
                            detail: { type: 'success', message: payload.message },
                        }));
                        window.location.reload();
                    } catch (error) {
                        window.dispatchEvent(new CustomEvent('app-toast', {
                            detail: { type: 'error', message: error.message || 'تعذر تنفيذ العملية الآن.' },
                        }));
                    } finally {
                        button.disabled = false;
                    }
                });
            });
        });
    </script>
</x-app-layout>



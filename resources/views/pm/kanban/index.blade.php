<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Kanban Board" description="Move work across the delivery flow with quick project and assignee filtering." />
    </x-slot>

    <div
        class="space-y-6"
        x-data="kanbanBoard()"
        x-init="init()"
        x-on:keydown.escape.window="closeTaskPanel()"
    >
        <form class="panel grid gap-4 lg:grid-cols-4">
            <select name="project_id">
                <option value="">{{ __('All projects') }}</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}" @selected((string) request('project_id') === (string) $project->id)>{{ $project->name }}</option>
                @endforeach
            </select>

            <select name="user_id">
                <option value="">{{ __('All assignees') }}</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" @selected((string) request('user_id') === (string) $user->id)>{{ $user->name }}</option>
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

        <div class="grid gap-4 xl:grid-cols-4">
            @foreach(\App\Models\Task::STATUSES as $status)
                <div class="rounded-[1.75rem] border border-white/10 bg-white/5 p-4" data-kanban-column>
                    <div class="mb-4 flex items-center justify-between">
                        <div class="text-sm font-semibold text-white">{{ __(str($status)->replace('_', ' ')->title()->toString()) }}</div>
                        <span data-kanban-count="{{ $status }}">
                            <x-status-badge :value="count($tasks[$status] ?? [])" :color="config('crm_pm.labels.task_statuses.' . $status . '.color', 'slate')" />
                        </span>
                    </div>

                    <div class="space-y-3" data-sortable-column="{{ $status }}">
                        @forelse(($tasks[$status] ?? collect()) as $task)
                            <a
                                href="{{ route('tasks.show', $task) }}"
                                class="block w-full rounded-2xl border border-white/10 bg-slate-950/60 p-4 text-right transition hover:border-cyan-400/40 hover:bg-slate-950/80 focus:outline-none focus:ring-2 focus:ring-cyan-500/30"
                                data-task-id="{{ $task->id }}"
                                data-task-card
                                @click.prevent="if ($el.dataset.dragging === '1') return; openTaskPanel({{ $task->id }}, '{{ route('tasks.show', $task) }}')"
                            >
                                <div class="text-sm font-semibold text-white">{{ $task->title }}</div>
                                <div class="mt-2 text-xs text-slate-400">{{ $task->project?->name }}</div>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <x-status-badge :value="$task->priority" :color="config('crm_pm.labels.priorities.' . $task->priority . '.color', 'slate')" />
                                </div>
                            </a>
                        @empty
                            <div
                                class="rounded-2xl border border-dashed border-white/10 px-4 py-6 text-center text-xs text-slate-500"
                                data-empty-state
                            >
                                {{ __('No tasks in this column.') }}
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>

        <div
            x-cloak
            x-show="taskPanelOpen"
            x-transition.opacity
            class="fixed inset-0 z-[240]"
            aria-hidden="true"
        >
            <div class="task-drawer-backdrop absolute inset-0" @click="closeTaskPanel()"></div>
        </div>

        <aside
            x-cloak
            x-show="taskPanelOpen"
            x-transition:enter="transform ease-out duration-200"
            x-transition:enter-start="{{ app()->getLocale() === 'ar' ? 'translate-x-full' : '-translate-x-full' }}"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transform ease-in duration-150"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="{{ app()->getLocale() === 'ar' ? 'translate-x-full' : '-translate-x-full' }}"
            class="task-drawer-panel fixed inset-y-0 {{ app()->getLocale() === 'ar' ? 'right-0' : 'left-0' }} z-[250] w-full max-w-[34rem] border-white/10 bg-slate-950 shadow-2xl"
        >
            <div x-show="taskPanelLoading" class="flex h-full items-center justify-center text-sm text-slate-400">
                {{ __('Loading task...') }}
            </div>

            <div x-show="! taskPanelLoading" x-ref="taskPanelBody" class="h-full"></div>
        </aside>
    </div>

    <script>
        window.kanbanBoard = () => ({
                init() {
                    this.$nextTick(() => {
                        if (!window.Sortable) {
                            return;
                        }

                        this.$root.querySelectorAll('[data-sortable-column]').forEach((column) => {
                            new window.Sortable(column, {
                                group: 'kanban',
                                animation: 180,
                                draggable: '[data-task-card]',
                                onStart: (event) => {
                                    event.item.dataset.dragging = '1';
                                },
                                onEnd: async (event) => {
                                    const taskId = event.item.dataset.taskId;
                                    const status = event.to.dataset.sortableColumn;
                                    const url = "{{ url('/kanban') }}/" + taskId + "/move";
                                    let moveSucceeded = false;

                                    try {
                                        this.refreshColumnState(event.from);
                                        this.refreshColumnState(event.to);

                                        const response = await fetch(url, {
                                            method: 'PATCH',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'Accept': 'application/json',
                                                'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]').content,
                                            },
                                            body: JSON.stringify({ status }),
                                        });

                                        if (response.ok) {
                                            moveSucceeded = true;
                                            window.dispatchEvent(new CustomEvent('app-toast', {
                                                detail: {
                                                    type: 'success',
                                                    message: @json(__('Task moved successfully.')),
                                                },
                                            }));
                                        }
                                    } catch (error) {
                                        // Fall through to restore the task in its original column.
                                    } finally {
                                        if (!moveSucceeded && (event.from !== event.to || !event.from.contains(event.item))) {
                                            event.from.appendChild(event.item);
                                        }

                                        this.refreshColumnState(event.from);
                                        this.refreshColumnState(event.to);

                                        window.setTimeout(() => {
                                            delete event.item.dataset.dragging;
                                        }, 50);
                                    }
                                }
                            });

                            this.refreshColumnState(column);
                        });
                    });
                },
                taskPanelOpen: false,
                taskPanelLoading: false,
                refreshColumnState(column) {
                    const emptyState = column.querySelector('[data-empty-state]');
                    const taskCount = column.querySelectorAll('[data-task-id]').length;

                    if (emptyState) {
                        emptyState.classList.toggle('hidden', taskCount > 0);
                    } else if (taskCount === 0) {
                        const placeholder = document.createElement('div');
                        placeholder.className = 'rounded-2xl border border-dashed border-white/10 px-4 py-6 text-center text-xs text-slate-500';
                        placeholder.dataset.emptyState = '';
                        placeholder.textContent = @json(__('No tasks in this column.'));
                        column.appendChild(placeholder);
                    }

                    const countStatus = column.dataset.sortableColumn;
                    const countWrapper = document.querySelector(`[data-kanban-count="${countStatus}"]`);
                    const countBadge = countWrapper?.querySelector('.status-badge');

                    if (countBadge) {
                        countBadge.textContent = taskCount;
                    }
                },
                showToast(message, type = 'success') {
                    window.dispatchEvent(new CustomEvent('app-toast', {
                        detail: { type, message },
                    }));
                },
                bindTaskPanelActions(taskId, fallbackUrl) {
                    this.$refs.taskPanelBody.querySelectorAll('[data-task-panel-close]').forEach((button) => {
                        button.addEventListener('click', () => this.closeTaskPanel(), { once: true });
                    });

                    this.$refs.taskPanelBody.querySelectorAll('[data-task-status-button]').forEach((button) => {
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
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    },
                                    body: JSON.stringify({ status }),
                                });

                                if (!response.ok) {
                                    throw new Error('Unable to update task status.');
                                }

                                const taskCard = document.querySelector(`[data-task-id="${taskId}"]`);
                                const sourceColumn = taskCard?.closest('[data-sortable-column]');
                                const targetColumn = document.querySelector(`[data-sortable-column="${status}"]`);

                                if (taskCard && sourceColumn && targetColumn && sourceColumn !== targetColumn) {
                                    targetColumn.appendChild(taskCard);
                                    this.refreshColumnState(sourceColumn);
                                    this.refreshColumnState(targetColumn);
                                }

                                await this.openTaskPanel(taskId, fallbackUrl);
                                this.showToast('تم تحديث حالة المهمة بنجاح.');
                            } catch (error) {
                                this.showToast('تعذر تحديث حالة المهمة الآن.', 'error');
                            } finally {
                                button.disabled = false;
                            }
                        });
                    });

                    this.$refs.taskPanelBody.querySelectorAll('[data-task-timer-action]').forEach((button) => {
                        button.addEventListener('click', async () => {
                            const url = button.dataset.taskTimerUrl;
                            button.disabled = true;

                            try {
                                const response = await fetch(url, {
                                    method: 'POST',
                                    headers: {
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    },
                                });

                                const payload = await response.json();

                                if (!response.ok || !payload.success) {
                                    throw new Error(payload.message || 'Unable to update timer.');
                                }

                                await this.openTaskPanel(taskId, fallbackUrl);
                                this.showToast(payload.message);
                            } catch (error) {
                                this.showToast(error.message || 'تعذر تنفيذ المؤقت الآن.', 'error');
                            } finally {
                                button.disabled = false;
                            }
                        });
                    });
                },
                async openTaskPanel(taskId, fallbackUrl = null) {
                    this.taskPanelOpen = true;
                    this.taskPanelLoading = true;

                    try {
                        const response = await fetch(`{{ url('/tasks') }}/${taskId}?panel=1`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'text/html',
                            },
                        });

                        if (!response.ok) {
                            throw new Error('Failed to load task panel.');
                        }

                        this.$refs.taskPanelBody.innerHTML = await response.text();
                        this.bindTaskPanelActions(taskId, fallbackUrl);

                        if (window.Alpine?.initTree) {
                            window.Alpine.initTree(this.$refs.taskPanelBody);
                        }

                        this.showToast(@json(__('Task details loaded successfully.')));
                    } catch (error) {
                        if (fallbackUrl) {
                            window.location.href = fallbackUrl;
                            return;
                        }

                        this.$refs.taskPanelBody.innerHTML = `<div class="flex h-full items-center justify-center px-6 text-sm text-rose-300">{{ __('Unable to load task details right now.') }}</div>`;
                        this.showToast(@json(__('Unable to load task details right now.')), 'error');
                    } finally {
                        this.taskPanelLoading = false;
                    }
                },
                closeTaskPanel() {
                    this.taskPanelOpen = false;
                    if (this.$refs.taskPanelBody) {
                        this.$refs.taskPanelBody.innerHTML = '';
                    }
                },
            });
    </script>
</x-app-layout>

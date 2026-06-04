<x-app-layout>
    <x-slot name="header">
        <x-page-header :title="__('Executive Dashboard')" :description="__('Track sales, delivery, deadlines, and team activity from one place.')">
            <a href="{{ route('customers.create') }}" class="btn-secondary">{{ __('New Customer') }}</a>
            <a href="{{ route('projects.create') }}" class="btn-primary">{{ __('New Project') }}</a>
        </x-page-header>
    </x-slot>

    <div class="space-y-6">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <x-stat-card :label="__('Customers')" :value="$stats['customers']" :hint="__('Managed customer records')" />
            <x-stat-card :label="__('Open deals')" :value="$stats['open_deals']" :hint="__('Active revenue opportunities')" accent="amber" />
            <x-stat-card :label="__('Projects')" :value="$stats['projects']" :hint="__('Projects currently being tracked')" accent="sky" />
            <x-stat-card :label="__('Overdue tasks')" :value="$stats['overdue_tasks']" :hint="__('Tasks that need follow-up')" accent="rose" />
        </div>

        <div class="grid gap-6 xl:grid-cols-[1.4fr_0.6fr]">
            <div class="panel">
                <div class="mb-5 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-white">{{ __('Sales overview') }}</h2>
                        <p class="text-sm text-slate-400">{{ __('Value distribution across deal stages.') }}</p>
                    </div>
                    <x-status-badge value="Live Data" color="emerald" />
                </div>
                <canvas id="salesChart" height="120"></canvas>
            </div>

            <div class="panel">
                <h2 class="text-lg font-semibold text-white">{{ __('Quick summary') }}</h2>
                <dl class="mt-5 space-y-4 text-sm">
                    <div class="flex items-center justify-between rounded-2xl bg-white/5 px-4 py-3">
                        <dt class="text-slate-400">{{ __('Won deals') }}</dt>
                        <dd class="font-semibold text-white">{{ $stats['won_deals'] }}</dd>
                    </div>
                    <div class="flex items-center justify-between rounded-2xl bg-white/5 px-4 py-3">
                        <dt class="text-slate-400">{{ __('Total sales') }}</dt>
                        <dd class="font-semibold text-white">${{ number_format($stats['sales_total'], 2) }}</dd>
                    </div>
                    <div class="flex items-center justify-between rounded-2xl bg-white/5 px-4 py-3">
                        <dt class="text-slate-400">{{ __('Late projects') }}</dt>
                        <dd class="font-semibold text-white">{{ $stats['late_projects'] }}</dd>
                    </div>
                    <div class="flex items-center justify-between rounded-2xl bg-white/5 px-4 py-3">
                        <dt class="text-slate-400">{{ __('Tasks') }}</dt>
                        <dd class="font-semibold text-white">{{ $stats['tasks'] }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-3">
            <div class="panel xl:col-span-2">
                <div class="mb-5 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-white">{{ __('Recent activity') }}</h2>
                    <a href="{{ route('audit-logs.index') }}" class="text-sm text-cyan-300">{{ __('View full audit log') }}</a>
                </div>
                <div class="space-y-4">
                    @forelse($recent_activities as $activity)
                        <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-4">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <div class="text-sm font-semibold text-white">{{ __($activity->description ?? str($activity->event)->replace('_', ' ')->title()->toString()) }}</div>
                                    <div class="mt-1 text-xs text-slate-400">{{ $activity->user?->name ?? __('System') }} | {{ __(str($activity->module)->replace('_', ' ')->title()->toString()) }}</div>
                                </div>
                                <div class="text-xs text-slate-500">{{ $activity->created_at?->diffForHumans() }}</div>
                            </div>
                        </div>
                    @empty
                        <x-empty-state :title="__('No activity yet')" :message="__('The audit trail will appear here once the team starts using the system.')" />
                    @endforelse
                </div>
            </div>

            <div class="panel">
                <h2 class="text-lg font-semibold text-white">{{ __('Upcoming deadlines') }}</h2>
                <div class="mt-5 space-y-3">
                    @forelse($upcoming_deadlines as $task)
                        <a href="{{ route('tasks.show', $task) }}" class="block rounded-2xl border border-white/10 bg-white/5 px-4 py-4 hover:bg-white/10">
                            <div class="text-sm font-semibold text-white">{{ $task->title }}</div>
                            <div class="mt-1 text-xs text-slate-400">{{ $task->project?->name }} | {{ optional($task->due_date)->format('Y-m-d') }}</div>
                        </a>
                    @empty
                        <x-empty-state :title="__('No upcoming deadlines')" :message="__('Tasks due in the next seven days will appear here.')" />
                    @endforelse
                </div>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-3">
            <div class="panel">
                <h2 class="text-lg font-semibold text-white">{{ __('Latest leads') }}</h2>
                <div class="mt-5 space-y-3">
                    @forelse($recent_leads as $lead)
                        <a href="{{ route('leads.show', $lead) }}" class="block rounded-2xl bg-white/5 px-4 py-4 hover:bg-white/10">
                            <div class="flex items-center justify-between">
                                <div class="text-sm font-semibold text-white">{{ $lead->name }}</div>
                                <x-status-badge :value="$lead->stage" :color="config('crm_pm.labels.lead_stages.' . $lead->stage . '.color', 'slate')" />
                            </div>
                            <div class="mt-1 text-xs text-slate-400">{{ $lead->company_name ?: __('Not specified') }}</div>
                        </a>
                    @empty
                        <x-empty-state :title="__('No leads yet')" :message="__('Recently added leads will appear here.')" />
                    @endforelse
                </div>
            </div>

            <div class="panel">
                <h2 class="text-lg font-semibold text-white">{{ __('Overdue tasks') }}</h2>
                <div class="mt-5 space-y-3">
                    @forelse($overdue_tasks as $task)
                        <a href="{{ route('tasks.show', $task) }}" class="block rounded-2xl bg-white/5 px-4 py-4 hover:bg-white/10">
                            <div class="flex items-center justify-between">
                                <div class="text-sm font-semibold text-white">{{ $task->title }}</div>
                                <x-status-badge :value="$task->priority" :color="config('crm_pm.labels.priorities.' . $task->priority . '.color', 'slate')" />
                            </div>
                            <div class="mt-1 text-xs text-slate-400">{{ $task->project?->name }} | {{ __('Due') }} {{ optional($task->due_date)->format('Y-m-d') }}</div>
                        </a>
                    @empty
                        <x-empty-state :title="__('No overdue tasks')" :message="__('Work is on track. Overdue tasks will only appear here when follow-up is needed.')" />
                    @endforelse
                </div>
            </div>

            <div class="panel">
                <h2 class="text-lg font-semibold text-white">{{ __('Team performance') }}</h2>
                <div class="mt-5 space-y-4">
                    @forelse($team_performance as $member)
                        <div class="rounded-2xl bg-white/5 px-4 py-4">
                            <div class="flex items-center justify-between">
                                <div class="text-sm font-semibold text-white">{{ $member->name }}</div>
                                <div class="text-xs text-slate-400">{{ __(':duration on tasks', ['duration' => \App\Support\Duration::fromMinutes($member->tracked_minutes_sum ?? 0)]) }}</div>
                            </div>
                            <div class="mt-3 grid grid-cols-2 gap-3 text-xs text-slate-400">
                                <div class="rounded-2xl bg-slate-900/50 px-3 py-2">{{ __('Completed') }}: <span class="font-semibold text-white">{{ $member->completed_tasks_count }}</span></div>
                                <div class="rounded-2xl bg-slate-900/50 px-3 py-2">{{ __('Attendance duration') }}: <span class="font-semibold text-white">{{ \App\Support\Duration::fromMinutes($member->attendance_minutes_sum ?? 0) }}</span></div>
                            </div>
                        </div>
                    @empty
                        <x-empty-state :title="__('No team data yet')" :message="__('Team data will appear here with task sessions and daily attendance.')" />
                    @endforelse
                </div>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-3">
            <div class="panel">
                <h2 class="text-lg font-semibold text-white">{{ __('Active now') }}</h2>
                <div class="mt-5 space-y-3">
                    @forelse($active_attendance as $record)
                        <div class="rounded-2xl bg-white/5 px-4 py-4">
                            <div class="flex items-center justify-between">
                                <div class="text-sm font-semibold text-white">{{ $record->user?->name }}</div>
                                <x-status-badge :value="__('At work')" color="emerald" />
                            </div>
                            <div class="mt-1 text-xs text-slate-400">{{ __('Started work at :time', ['time' => $record->checked_in_at?->format('H:i')]) }}</div>
                        </div>
                    @empty
                        <x-empty-state :title="__('No live attendance')" :message="__('People who checked in and have not checked out yet will appear here.')" />
                    @endforelse
                </div>
            </div>

            <div class="panel">
                <h2 class="text-lg font-semibold text-white">{{ __('Active timers') }}</h2>
                <div class="mt-5 space-y-3">
                    @forelse($active_task_timers as $entry)
                        <div class="rounded-2xl bg-white/5 px-4 py-4">
                            <div class="flex items-center justify-between">
                                <div class="text-sm font-semibold text-white">{{ $entry->user?->name }}</div>
                                <div class="text-xs text-cyan-300">{{ $entry->started_at?->diffForHumans() }}</div>
                            </div>
                            <div class="mt-1 text-xs text-slate-400">{{ $entry->task?->title ?: __('No task') }}</div>
                            <div class="mt-1 text-[11px] text-slate-500">{{ $entry->project?->name }}</div>
                        </div>
                    @empty
                        <x-empty-state :title="__('No timers running now')" :message="__('People currently working on their tasks will appear here in real time.')" />
                    @endforelse
                </div>
            </div>

            <div class="panel">
                <h2 class="text-lg font-semibold text-white">{{ __('Risk signals') }}</h2>
                <div class="mt-5 space-y-4 text-sm">
                    <div class="rounded-2xl bg-white/5 px-4 py-4">
                        <div class="text-slate-400">{{ __('Tasks in progress or review') }}</div>
                        <div class="mt-2 text-2xl font-semibold text-white">{{ $risk_signals['many_in_progress'] }}</div>
                    </div>
                    <div class="rounded-2xl bg-white/5 px-4 py-4">
                        <div class="text-slate-400">{{ __('Tasks closed today') }}</div>
                        <div class="mt-2 text-2xl font-semibold text-white">{{ $risk_signals['completed_today'] }}</div>
                    </div>
                    <div class="rounded-2xl bg-white/5 px-4 py-4">
                        <div class="text-slate-400">{{ __('Today hours: attendance vs tasks') }}</div>
                        <div class="mt-2 text-sm text-white">
                            {{ __('Attendance') }}: {{ \App\Support\Duration::fromMinutes($risk_signals['attendance_minutes_today']) }}
                            <br>
                            {{ __('Tasks') }}: {{ \App\Support\Duration::fromMinutes($risk_signals['tracked_minutes_today']) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const ctx = document.getElementById('salesChart');
            if (!ctx || !window.Chart) return;

            new window.Chart(ctx, {
                type: 'bar',
                data: {
                    labels: @json(collect(array_keys($sales_by_stage))->map(fn ($label) => __($label))->values()),
                    datasets: [{
                        label: @json(__('Deal value')),
                        data: @json(array_values($sales_by_stage)),
                        borderRadius: 12,
                        backgroundColor: ['#22d3ee', '#38bdf8', '#818cf8', '#f59e0b', '#fb923c', '#10b981', '#f43f5e'],
                    }]
                },
                options: {
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { ticks: { color: '#94a3b8' }, grid: { display: false } },
                        y: { ticks: { color: '#94a3b8' }, grid: { color: 'rgba(148,163,184,0.12)' } }
                    }
                }
            });
        });
    </script>
</x-app-layout>



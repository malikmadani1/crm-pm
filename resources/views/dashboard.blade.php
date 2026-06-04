<x-app-layout>
    <x-slot name="header">
        <x-page-header title="لوحة التحكم التنفيذية" description="تابع المبيعات والتنفيذ والمواعيد ونشاط الفريق من مكان واحد.">
            <a href="{{ route('customers.create') }}" class="btn-secondary">عميل جديد</a>
            <a href="{{ route('projects.create') }}" class="btn-primary">مشروع جديد</a>
        </x-page-header>
    </x-slot>

    <div class="space-y-6">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <x-stat-card label="العملاء" :value="$stats['customers']" hint="سجلات العملاء المدارة" />
            <x-stat-card label="فرص البيع المفتوحة" :value="$stats['open_deals']" hint="فرص البيع النشطة" accent="amber" />
            <x-stat-card label="المشاريع" :value="$stats['projects']" hint="المشاريع الجاري متابعتها" accent="sky" />
            <x-stat-card label="المهام المتأخرة" :value="$stats['overdue_tasks']" hint="مهام تحتاج متابعة" accent="rose" />
        </div>

        <div class="grid gap-6 xl:grid-cols-[1.4fr_0.6fr]">
            <div class="panel">
                <div class="mb-5 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-white">نظرة على المبيعات</h2>
                        <p class="text-sm text-slate-400">توزيع القيمة عبر مراحل فرص البيع.</p>
                    </div>
                    <x-status-badge value="Live Data" color="emerald" />
                </div>
                <canvas id="salesChart" height="120"></canvas>
            </div>

            <div class="panel">
                <h2 class="text-lg font-semibold text-white">ملخص سريع</h2>
                <dl class="mt-5 space-y-4 text-sm">
                    <div class="flex items-center justify-between rounded-2xl bg-white/5 px-4 py-3">
                        <dt class="text-slate-400">فرص البيع الرابحة</dt>
                        <dd class="font-semibold text-white">{{ $stats['won_deals'] }}</dd>
                    </div>
                    <div class="flex items-center justify-between rounded-2xl bg-white/5 px-4 py-3">
                        <dt class="text-slate-400">إجمالي المبيعات</dt>
                        <dd class="font-semibold text-white">${{ number_format($stats['sales_total'], 2) }}</dd>
                    </div>
                    <div class="flex items-center justify-between rounded-2xl bg-white/5 px-4 py-3">
                        <dt class="text-slate-400">المشاريع المتأخرة</dt>
                        <dd class="font-semibold text-white">{{ $stats['late_projects'] }}</dd>
                    </div>
                    <div class="flex items-center justify-between rounded-2xl bg-white/5 px-4 py-3">
                        <dt class="text-slate-400">المهام</dt>
                        <dd class="font-semibold text-white">{{ $stats['tasks'] }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-3">
            <div class="panel xl:col-span-2">
                <div class="mb-5 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-white">أحدث الأنشطة</h2>
                    <a href="{{ route('audit-logs.index') }}" class="text-sm text-cyan-300">عرض السجل الكامل</a>
                </div>
                <div class="space-y-4">
                    @forelse($recent_activities as $activity)
                        <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-4">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <div class="text-sm font-semibold text-white">{{ __($activity->description ?? str($activity->event)->replace('_', ' ')->title()->toString()) }}</div>
                                    <div class="mt-1 text-xs text-slate-400">{{ $activity->user?->name ?? 'النظام' }} | {{ __(str($activity->module)->replace('_', ' ')->title()->toString()) }}</div>
                                </div>
                                <div class="text-xs text-slate-500">{{ $activity->created_at?->diffForHumans() }}</div>
                            </div>
                        </div>
                    @empty
                        <x-empty-state title="لا يوجد نشاط بعد" message="سيظهر سجل العمليات هنا بمجرد بدء استخدام الفريق للنظام." />
                    @endforelse
                </div>
            </div>

            <div class="panel">
                <h2 class="text-lg font-semibold text-white">المواعيد القادمة</h2>
                <div class="mt-5 space-y-3">
                    @forelse($upcoming_deadlines as $task)
                        <a href="{{ route('tasks.show', $task) }}" class="block rounded-2xl border border-white/10 bg-white/5 px-4 py-4 hover:bg-white/10">
                            <div class="text-sm font-semibold text-white">{{ $task->title }}</div>
                            <div class="mt-1 text-xs text-slate-400">{{ $task->project?->name }} | {{ optional($task->due_date)->format('Y-m-d') }}</div>
                        </a>
                    @empty
                        <x-empty-state title="لا توجد مواعيد قادمة" message="ستظهر هنا المهام المستحقة خلال الأيام السبعة القادمة." />
                    @endforelse
                </div>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-3">
            <div class="panel">
                <h2 class="text-lg font-semibold text-white">أحدث العملاء المحتملين</h2>
                <div class="mt-5 space-y-3">
                    @forelse($recent_leads as $lead)
                        <a href="{{ route('leads.show', $lead) }}" class="block rounded-2xl bg-white/5 px-4 py-4 hover:bg-white/10">
                            <div class="flex items-center justify-between">
                                <div class="text-sm font-semibold text-white">{{ $lead->name }}</div>
                                <x-status-badge :value="$lead->stage" :color="config('crm_pm.labels.lead_stages.' . $lead->stage . '.color', 'slate')" />
                            </div>
                            <div class="mt-1 text-xs text-slate-400">{{ $lead->company_name ?: 'غير محدد' }}</div>
                        </a>
                    @empty
                        <x-empty-state title="لا يوجد عملاء محتملون بعد" message="سيظهر هنا آخر العملاء المحتملين المضافين." />
                    @endforelse
                </div>
            </div>

            <div class="panel">
                <h2 class="text-lg font-semibold text-white">المهام المتأخرة</h2>
                <div class="mt-5 space-y-3">
                    @forelse($overdue_tasks as $task)
                        <a href="{{ route('tasks.show', $task) }}" class="block rounded-2xl bg-white/5 px-4 py-4 hover:bg-white/10">
                            <div class="flex items-center justify-between">
                                <div class="text-sm font-semibold text-white">{{ $task->title }}</div>
                                <x-status-badge :value="$task->priority" :color="config('crm_pm.labels.priorities.' . $task->priority . '.color', 'slate')" />
                            </div>
                            <div class="mt-1 text-xs text-slate-400">{{ $task->project?->name }} | الاستحقاق {{ optional($task->due_date)->format('Y-m-d') }}</div>
                        </a>
                    @empty
                        <x-empty-state title="لا توجد مهام متأخرة" message="إيقاع العمل ممتاز. ستظهر المهام المتأخرة هنا فقط عند الحاجة إلى متابعة." />
                    @endforelse
                </div>
            </div>

            <div class="panel">
                <h2 class="text-lg font-semibold text-white">أداء الفريق</h2>
                <div class="mt-5 space-y-4">
                    @forelse($team_performance as $member)
                        <div class="rounded-2xl bg-white/5 px-4 py-4">
                            <div class="flex items-center justify-between">
                                <div class="text-sm font-semibold text-white">{{ $member->name }}</div>
                                <div class="text-xs text-slate-400">{{ \App\Support\Duration::fromMinutes($member->tracked_minutes_sum ?? 0) }} على المهام</div>
                            </div>
                            <div class="mt-3 grid grid-cols-2 gap-3 text-xs text-slate-400">
                                <div class="rounded-2xl bg-slate-900/50 px-3 py-2">المكتمل: <span class="font-semibold text-white">{{ $member->completed_tasks_count }}</span></div>
                                <div class="rounded-2xl bg-slate-900/50 px-3 py-2">مدة الدوام: <span class="font-semibold text-white">{{ \App\Support\Duration::fromMinutes($member->attendance_minutes_sum ?? 0) }}</span></div>
                            </div>
                        </div>
                    @empty
                        <x-empty-state title="لا توجد بيانات فريق بعد" message="ستظهر هنا بيانات الفريق مع جلسات العمل على المهام والحضور اليومي." />
                    @endforelse
                </div>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-3">
            <div class="panel">
                <h2 class="text-lg font-semibold text-white">الموجودون الآن</h2>
                <div class="mt-5 space-y-3">
                    @forelse($active_attendance as $record)
                        <div class="rounded-2xl bg-white/5 px-4 py-4">
                            <div class="flex items-center justify-between">
                                <div class="text-sm font-semibold text-white">{{ $record->user?->name }}</div>
                                <x-status-badge value="على رأس العمل" color="emerald" />
                            </div>
                            <div class="mt-1 text-xs text-slate-400">بدأ الدوام {{ $record->checked_in_at?->format('H:i') }}</div>
                        </div>
                    @empty
                        <x-empty-state title="لا يوجد حضور مباشر" message="سيظهر هنا من بدأ الدوام ولم يسجل خروجًا بعد." />
                    @endforelse
                </div>
            </div>

            <div class="panel">
                <h2 class="text-lg font-semibold text-white">المؤقتات النشطة</h2>
                <div class="mt-5 space-y-3">
                    @forelse($active_task_timers as $entry)
                        <div class="rounded-2xl bg-white/5 px-4 py-4">
                            <div class="flex items-center justify-between">
                                <div class="text-sm font-semibold text-white">{{ $entry->user?->name }}</div>
                                <div class="text-xs text-cyan-300">{{ $entry->started_at?->diffForHumans() }}</div>
                            </div>
                            <div class="mt-1 text-xs text-slate-400">{{ $entry->task?->title ?: 'بدون مهمة' }}</div>
                            <div class="mt-1 text-[11px] text-slate-500">{{ $entry->project?->name }}</div>
                        </div>
                    @empty
                        <x-empty-state title="لا توجد مؤقتات تعمل الآن" message="سيظهر هنا من يعمل الآن على مهامه بشكل مباشر." />
                    @endforelse
                </div>
            </div>

            <div class="panel">
                <h2 class="text-lg font-semibold text-white">إشارات الخطر</h2>
                <div class="mt-5 space-y-4 text-sm">
                    <div class="rounded-2xl bg-white/5 px-4 py-4">
                        <div class="text-slate-400">مهام قيد التنفيذ أو المراجعة</div>
                        <div class="mt-2 text-2xl font-semibold text-white">{{ $risk_signals['many_in_progress'] }}</div>
                    </div>
                    <div class="rounded-2xl bg-white/5 px-4 py-4">
                        <div class="text-slate-400">مهام أغلقت اليوم</div>
                        <div class="mt-2 text-2xl font-semibold text-white">{{ $risk_signals['completed_today'] }}</div>
                    </div>
                    <div class="rounded-2xl bg-white/5 px-4 py-4">
                        <div class="text-slate-400">ساعات اليوم: دوام مقابل مهام</div>
                        <div class="mt-2 text-sm text-white">
                            دوام: {{ \App\Support\Duration::fromMinutes($risk_signals['attendance_minutes_today']) }}
                            <br>
                            مهام: {{ \App\Support\Duration::fromMinutes($risk_signals['tracked_minutes_today']) }}
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
                        label: @json('قيمة فرص البيع'),
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



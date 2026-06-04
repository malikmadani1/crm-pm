<x-app-layout>
    <x-slot name="header">
        <x-page-header :title="$user->name" description="الملف الشخصي والمسؤوليات وعبء العمل وآخر النشاطات لهذا المستخدم.">
            @can('update', $user)
                <a href="{{ route('users.edit', $user) }}" class="btn-secondary">{{ __('Edit') }}</a>
            @endcan
            @can('delete', $user)
                @if($user->isProtectedSuperAdmin())
                    <span class="rounded-xl border border-amber-400/20 bg-amber-500/10 px-3 py-2 text-xs font-semibold text-amber-300">
                        المستخدم الإداري المحمي
                    </span>
                @elseif(auth()->id() === $user->id)
                    <span class="rounded-xl border border-slate-400/20 bg-slate-500/10 px-3 py-2 text-xs font-semibold text-slate-300">
                        الجلسة الحالية
                    </span>
                @else
                    <x-delete-action
                        :action="route('users.destroy', $user)"
                        :title="__('Delete user')"
                        :message="__('Are you sure you want to delete this user?')"
                    />
                @endif
            @endcan
        </x-page-header>
    </x-slot>

    <div class="grid gap-6 xl:grid-cols-[0.7fr_1.3fr]">
        <div class="space-y-6">
            <div class="panel">
                <div class="flex items-center gap-4">
                    <div class="flex h-16 w-16 items-center justify-center rounded-3xl bg-cyan-500/20 text-2xl font-bold text-cyan-300">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                    <div>
                        <div class="text-xl font-semibold text-white">{{ $user->name }}</div>
                        <div class="text-sm text-slate-400">{{ $user->email }}</div>
                    </div>
                </div>

                <dl class="mt-6 space-y-3 text-sm">
                    <div class="flex justify-between"><dt class="text-slate-400">{{ __('Job Title') }}</dt><dd>{{ $user->job_title ? __($user->job_title) : __('N/A') }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-400">{{ __('Phone') }}</dt><dd>{{ $user->phone ?: __('N/A') }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-400">{{ __('Roles') }}</dt><dd>{{ collect($user->role_names)->map(fn ($roleName) => __($roleName))->join(', ') ?: __('N/A') }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-400">{{ __('Completed Tasks') }}</dt><dd>{{ $user->completed_tasks_count }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-400">وقت المهام</dt><dd>{{ \App\Support\Duration::fromMinutes($user->timeEntries->sum('minutes')) }}</dd></div>
                </dl>
            </div>

            <div class="panel">
                <h3 class="text-lg font-semibold text-white">آخر النشاطات</h3>
                <div class="mt-4 space-y-3">
                    @forelse($recentActivity as $activity)
                        <div class="rounded-2xl bg-white/5 px-4 py-3">
                            <div class="text-sm font-semibold text-white">{{ __($activity->description ?? str($activity->event)->replace('_', ' ')->title()->toString()) }}</div>
                            <div class="mt-1 text-xs text-slate-400">{{ $activity->created_at?->diffForHumans() }}</div>
                        </div>
                    @empty
                        <div class="text-sm text-slate-400">لا يوجد نشاط مسجل بعد.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="panel">
                <h3 class="text-lg font-semibold text-white">المهام المسندة</h3>
                <div class="mt-4 space-y-3">
                    @forelse($user->tasks as $task)
                        <a href="{{ route('tasks.show', $task) }}" class="block rounded-2xl bg-white/5 px-4 py-4 hover:bg-white/10">
                            <div class="flex items-center justify-between">
                                <div class="font-semibold text-white">{{ $task->title }}</div>
                                <x-status-badge :value="$task->status" :color="config('crm_pm.labels.task_statuses.' . $task->status . '.color', 'slate')" />
                            </div>
                            <div class="mt-1 text-xs text-slate-400">{{ $task->project?->name }}</div>
                        </a>
                    @empty
                        <div class="text-sm text-slate-400">لا توجد مهام مسندة.</div>
                    @endforelse
                </div>
            </div>

            <div class="panel">
                <h3 class="text-lg font-semibold text-white">المشاريع المرتبطة</h3>
                <div class="mt-4 space-y-3">
                    @forelse($user->projects as $project)
                        <a href="{{ route('projects.show', $project) }}" class="block rounded-2xl bg-white/5 px-4 py-4 hover:bg-white/10">
                            <div class="flex items-center justify-between">
                                <div class="font-semibold text-white">{{ $project->name }}</div>
                                <div class="text-xs text-slate-400">{{ $project->progress }}%</div>
                            </div>
                            <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-800">
                                <div class="h-full rounded-full bg-cyan-400" style="width: {{ $project->progress }}%"></div>
                            </div>
                        </a>
                    @empty
                        <div class="text-sm text-slate-400">لا توجد مشاريع مسندة بعد.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>


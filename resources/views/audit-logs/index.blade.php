<x-app-layout>
    <x-slot name="header">
        <x-page-header title="سجل العمليات" description="تابع كل عملية تمت داخل المنصة مع توضيح من قام بها وماذا تغيّر." />
    </x-slot>

    <div class="space-y-6">
        <form class="panel grid gap-4 lg:grid-cols-5">
            <select name="user_id">
                <option value="">كل المستخدمين</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" @selected((string) request('user_id') === (string) $user->id)>{{ $user->name }}</option>
                @endforeach
            </select>

            <select name="module">
                <option value="">كل الأقسام</option>
                @foreach($modules as $module)
                    <option value="{{ $module }}" @selected(request('module') === $module)>{{ \App\Models\AuditLog::make(['module' => $module])->moduleLabel() }}</option>
                @endforeach
            </select>

            <select name="event">
                <option value="">كل العمليات</option>
                @foreach($events as $event)
                    <option value="{{ $event }}" @selected(request('event') === $event)>{{ \App\Models\AuditLog::make(['event' => $event])->eventLabel() }}</option>
                @endforeach
            </select>

            <input type="date" name="from" value="{{ request('from') }}">

            <button class="btn-secondary">تطبيق التصفية</button>
        </form>

        @if($auditLogs->count())
            <div class="table-shell">
                <table>
                    <thead>
                        <tr>
                            <th>العملية</th>
                            <th>القسم</th>
                            <th>المستخدم</th>
                            <th>التاريخ</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($auditLogs as $log)
                            <tr>
                                <td>
                                    <div class="font-semibold text-white">{{ $log->eventLabel() }}</div>
                                    @if($log->subjectLabel())
                                        <div class="mt-1 text-xs text-slate-400">
                                            على:
                                            @if($log->subjectUrl())
                                                <a href="{{ $log->subjectUrl() }}" class="text-cyan-300 hover:text-cyan-200">{{ $log->subjectLabel() }}</a>
                                            @else
                                                {{ $log->subjectLabel() }}
                                            @endif
                                        </div>
                                    @endif
                                    @if($log->summaryLines() !== [])
                                        <div class="mt-2 space-y-1 text-xs text-slate-400">
                                            @foreach($log->summaryLines(2) as $line)
                                                <div>{{ $line }}</div>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td>{{ $log->moduleLabel() }}</td>
                                <td>{{ $log->user?->name ?: 'النظام' }}</td>
                                <td>
                                    <div>{{ $log->created_at?->format('Y-m-d H:i') }}</div>
                                    <div class="mt-1 text-xs text-slate-500">{{ $log->created_at?->diffForHumans() }}</div>
                                </td>
                                <td class="text-right"><a href="{{ route('audit-logs.show', $log) }}" class="text-cyan-300">التفاصيل</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $auditLogs->links() }}
        @else
            <x-empty-state title="لا توجد عمليات بعد" message="ستظهر هنا كل العمليات المهمة بمجرد البدء باستخدام المنصة." />
        @endif
    </div>
</x-app-layout>

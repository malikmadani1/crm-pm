<x-app-layout>
    <x-slot name="header">
        <x-page-header :title="$auditLog->eventLabel()" description="عرض تفصيلي لما تغيّر داخل هذه العملية." />
    </x-slot>

    <div class="space-y-6">
        <div class="panel">
            <dl class="grid gap-4 text-sm md:grid-cols-2">
                <div class="rounded-2xl bg-white/5 p-4">
                    <dt class="text-slate-400">العملية</dt>
                    <dd class="mt-2 font-semibold text-white">{{ $auditLog->eventLabel() }}</dd>
                </div>
                <div class="rounded-2xl bg-white/5 p-4">
                    <dt class="text-slate-400">القسم</dt>
                    <dd class="mt-2 font-semibold text-white">{{ $auditLog->moduleLabel() }}</dd>
                </div>
                <div class="rounded-2xl bg-white/5 p-4">
                    <dt class="text-slate-400">المستخدم</dt>
                    <dd class="mt-2 font-semibold text-white">{{ $auditLog->user?->name ?: 'النظام' }}</dd>
                </div>
                <div class="rounded-2xl bg-white/5 p-4">
                    <dt class="text-slate-400">التاريخ</dt>
                    <dd class="mt-2 font-semibold text-white">{{ $auditLog->created_at?->format('Y-m-d H:i:s') }}</dd>
                </div>
            </dl>

            @if($auditLog->subjectLabel())
                <div class="mt-4 rounded-2xl bg-white/5 p-4 text-sm text-slate-300">
                    العنصر المرتبط:
                    @if($auditLog->subjectUrl())
                        <a href="{{ $auditLog->subjectUrl() }}" class="font-medium text-cyan-300 hover:text-cyan-200">{{ $auditLog->subjectLabel() }}</a>
                    @else
                        <span class="font-medium text-white">{{ $auditLog->subjectLabel() }}</span>
                    @endif
                </div>
            @endif

            <div class="mt-4 rounded-2xl bg-white/5 p-4 text-sm text-slate-300">
                {{ $auditLog->description ?: 'لا يوجد وصف إضافي لهذه العملية.' }}
            </div>
        </div>

        <div class="panel">
            <h3 class="text-lg font-semibold text-white">تفاصيل التغيير</h3>

            @if($auditLog->changeRows() !== [])
                <div class="mt-4 overflow-hidden rounded-2xl border border-white/10">
                    <table class="w-full text-sm">
                        <thead class="bg-white/5 text-slate-300">
                            <tr>
                                <th class="px-4 py-3 text-right">الحقل</th>
                                <th class="px-4 py-3 text-right">القيمة السابقة</th>
                                <th class="px-4 py-3 text-right">القيمة الجديدة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($auditLog->changeRows() as $row)
                                <tr class="border-t border-white/10">
                                    <td class="px-4 py-3 font-medium text-white">{{ $row['field'] }}</td>
                                    <td class="px-4 py-3 text-slate-300">{{ $row['old'] ?? 'فارغ' }}</td>
                                    <td class="px-4 py-3 text-slate-300">{{ $row['new'] ?? 'فارغ' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="mt-4 rounded-2xl bg-white/5 p-4 text-sm text-slate-300">
                    {{ $auditLog->description ?: 'لا توجد فروقات محفوظة لهذه العملية.' }}
                </div>
            @endif
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="panel">
                <h3 class="text-lg font-semibold text-white">ملخص القيم السابقة</h3>
                <div class="mt-4 space-y-3">
                    @forelse($auditLog->changeRows() as $row)
                        <div class="rounded-2xl bg-slate-950/40 px-4 py-3">
                            <div class="text-xs text-slate-400">{{ $row['field'] }}</div>
                            <div class="mt-1 text-sm text-white">{{ $row['old'] ?? 'فارغ' }}</div>
                        </div>
                    @empty
                        <div class="rounded-2xl bg-white/5 p-4 text-sm text-slate-300">لا توجد قيم سابقة محفوظة.</div>
                    @endforelse
                </div>
            </div>

            <div class="panel">
                <h3 class="text-lg font-semibold text-white">ملخص القيم الجديدة</h3>
                <div class="mt-4 space-y-3">
                    @forelse($auditLog->changeRows() as $row)
                        <div class="rounded-2xl bg-slate-950/40 px-4 py-3">
                            <div class="text-xs text-slate-400">{{ $row['field'] }}</div>
                            <div class="mt-1 text-sm text-white">{{ $row['new'] ?? 'فارغ' }}</div>
                        </div>
                    @empty
                        <div class="rounded-2xl bg-white/5 p-4 text-sm text-slate-300">لا توجد قيم جديدة محفوظة.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

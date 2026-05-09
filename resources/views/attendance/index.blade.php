<x-app-layout>
    <x-slot name="header">
        <x-page-header title="الحضور والانصراف" description="اعرف متى دخل كل موظف ومتى خرج وكم ساعة داوم في كل يوم." />
    </x-slot>

    <div class="space-y-6">
        <div class="grid gap-4 md:grid-cols-3">
            <x-stat-card label="أيام مسجلة" :value="$summary['days_count']" hint="عدد أيام الحضور المطابقة للتصفية" />
            <x-stat-card label="دوامات مفتوحة" :value="$summary['open_days_count']" hint="أيام تم تسجيل دخولها بدون خروج بعد" accent="amber" />
            <x-stat-card label="إجمالي ساعات الدوام" :value="$summary['worked_hours']" hint="إجمالي الساعات في النتائج الحالية" accent="sky" />
        </div>

        <form class="panel grid gap-4 lg:grid-cols-4">
            <select name="user_id">
                <option value="">كل الموظفين</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" @selected((string) request('user_id') === (string) $user->id)>{{ $user->name }}</option>
                @endforeach
            </select>

            <input type="date" name="from" value="{{ request('from') }}">
            <input type="date" name="to" value="{{ request('to') }}">

            <button class="btn-secondary">تطبيق التصفية</button>
        </form>

        @if($records->count())
            <div class="table-shell">
                <table>
                    <thead>
                        <tr>
                            <th>الموظف</th>
                            <th>التاريخ</th>
                            <th>وقت الدخول</th>
                            <th>وقت الخروج</th>
                            <th>ساعات الدوام</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($records as $record)
                            <tr>
                                <td>{{ $record->user?->name }}</td>
                                <td>{{ $record->work_date?->format('Y-m-d') }}</td>
                                <td>{{ $record->checked_in_at?->format('H:i') ?: 'غير مسجل' }}</td>
                                <td>{{ $record->checked_out_at?->format('H:i') ?: 'لم يخرج بعد' }}</td>
                                <td>{{ round(($record->worked_minutes ?? 0) / 60, 2) }} ساعة</td>
                                <td>
                                    @if($record->checked_in_at && ! $record->checked_out_at)
                                        <x-status-badge value="داخل الدوام" color="amber" />
                                    @elseif($record->checked_in_at && $record->checked_out_at)
                                        <x-status-badge value="منتهي" color="emerald" />
                                    @else
                                        <x-status-badge value="غير مكتمل" color="rose" />
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $records->links() }}
        @else
            <x-empty-state title="لا توجد سجلات حضور بعد" message="ستظهر هنا سجلات دخول وخروج الموظفين بمجرد بدء استخدام النظام." />
        @endif
    </div>
</x-app-layout>

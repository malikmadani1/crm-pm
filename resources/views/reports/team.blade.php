<x-app-layout>
    <x-slot name="header">
        <x-page-header title="تقرير الفريق" description="ملخص إنجاز الفريق مع وقت العمل على المهام والحضور اليومي." />
    </x-slot>

    <div class="table-shell">
        <table>
            <thead>
                <tr>
                    <th>العضو</th>
                    <th>المهام المكتملة</th>
                    <th>ساعات المهام</th>
                    <th>أيام الحضور</th>
                    <th>ساعات الدوام</th>
                </tr>
            </thead>
            <tbody>
                @foreach($report['items'] as $member)
                    <tr>
                        <td>{{ $member->name }}</td>
                        <td>{{ $member->completed_tasks_count }}</td>
                        <td>{{ round(($member->tracked_minutes_sum ?? 0) / 60, 1) }} ساعة</td>
                        <td>{{ $member->attendance_days_count }}</td>
                        <td>{{ round(($member->attendance_minutes_sum ?? 0) / 60, 1) }} ساعة</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $report['items']->links() }}
</x-app-layout>

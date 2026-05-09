<x-app-layout>
    <x-slot name="header">
        <x-page-header title="تقرير الفريق" description="ملخص إنجاز الفريق مع وقت العمل على المهام والحضور اليومي." />
    </x-slot>

    <div class="space-y-6">
        <div class="grid gap-4 md:grid-cols-3">
            <x-stat-card label="ساعات الدوام" :value="\App\Support\Duration::fromHours($report['summary']['attendance_hours'])" />
            <x-stat-card label="ساعات المهام" :value="\App\Support\Duration::fromHours($report['summary']['tracked_hours'])" accent="sky" />
            <x-stat-card label="نسبة الاستفادة" :value="$report['summary']['utilization'].'%'" accent="amber" />
        </div>

        <div class="table-shell">
        <table>
            <thead>
                <tr>
                    <th>العضو</th>
                    <th>المهام المكتملة</th>
                    <th>ساعات المهام</th>
                    <th>أيام الحضور</th>
                    <th>ساعات الدوام</th>
                    <th>نسبة الاستفادة</th>
                </tr>
            </thead>
            <tbody>
                @foreach($report['items'] as $member)
                    @php
                        $attendanceMinutes = (int) ($member->attendance_minutes_sum ?? 0);
                        $trackedMinutes = (int) ($member->tracked_minutes_sum ?? 0);
                        $utilization = $attendanceMinutes > 0 ? round(($trackedMinutes / $attendanceMinutes) * 100, 1) : 0;
                    @endphp
                    <tr>
                        <td>{{ $member->name }}</td>
                        <td>{{ $member->completed_tasks_count }}</td>
                        <td>{{ \App\Support\Duration::fromMinutes($member->tracked_minutes_sum ?? 0) }}</td>
                        <td>{{ $member->attendance_days_count }}</td>
                        <td>{{ \App\Support\Duration::fromMinutes($member->attendance_minutes_sum ?? 0) }}</td>
                        <td>{{ $utilization }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>

        {{ $report['items']->links() }}
    </div>
</x-app-layout>



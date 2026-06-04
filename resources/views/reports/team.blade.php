<x-app-layout>
    <x-slot name="header">
        <x-page-header :title="__('Team Report')" :description="__('Summarize team delivery with task work time and daily attendance.')" />
    </x-slot>

    <div class="space-y-6">
        <div class="grid gap-4 md:grid-cols-3">
            <x-stat-card :label="__('Attendance hours')" :value="\App\Support\Duration::fromHours($report['summary']['attendance_hours'])" />
            <x-stat-card :label="__('Task hours')" :value="\App\Support\Duration::fromHours($report['summary']['tracked_hours'])" accent="sky" />
            <x-stat-card :label="__('Utilization')" :value="$report['summary']['utilization'].'%'" accent="amber" />
        </div>

        <div class="table-shell">
        <table>
            <thead>
                <tr>
                    <th>{{ __('Member') }}</th>
                    <th>{{ __('Completed tasks') }}</th>
                    <th>{{ __('Task hours') }}</th>
                    <th>{{ __('Attendance days') }}</th>
                    <th>{{ __('Attendance hours') }}</th>
                    <th>{{ __('Utilization') }}</th>
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



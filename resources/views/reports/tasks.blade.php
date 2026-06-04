<x-app-layout>
    <x-slot name="header">
        <x-page-header :title="__('Task Report')" :description="__('Track delivery quality, delays, and actual time spent on tasks.')">
            <a href="{{ route('reports.export', 'tasks') }}" class="btn-secondary">{{ __('Export CSV') }}</a>
        </x-page-header>
    </x-slot>

    <div class="space-y-6">
        <div class="grid gap-4 md:grid-cols-4">
            <x-stat-card :label="__('Completed')" :value="$report['summary']['done_count']" accent="emerald" />
            <x-stat-card :label="__('Overdue')" :value="$report['summary']['overdue_count']" accent="rose" />
            <x-stat-card :label="__('Average completion')" :value="$report['summary']['avg_completion'].'%'" accent="sky" />
            <x-stat-card :label="__('Total task hours')" :value="\App\Support\Duration::fromHours($report['summary']['tracked_hours_total'])" accent="amber" />
        </div>

        <div class="table-shell">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('Task') }}</th>
                        <th>{{ __('Project') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Priority') }}</th>
                        <th>{{ __('Due Date') }}</th>
                        <th>{{ __('Actual Hours') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($report['items'] as $task)
                        <tr>
                            <td>
                                <div class="font-semibold text-white">{{ $task->title }}</div>
                                <div class="mt-1 text-xs text-slate-400">{{ $task->assignees->pluck('name')->join(\App\Support\Labels::separator()) ?: __('Unassigned') }}</div>
                            </td>
                            <td>{{ $task->project?->name }}</td>
                            <td>{{ __(str($task->status)->replace('_', ' ')->title()->toString()) }}</td>
                            <td>{{ __(str($task->priority)->title()->toString()) }}</td>
                            <td>{{ optional($task->due_date)->format('Y-m-d') }}</td>
                            <td>{{ \App\Support\Duration::fromMinutes($task->tracked_minutes_sum ?? 0) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $report['items']->links() }}
    </div>
</x-app-layout>


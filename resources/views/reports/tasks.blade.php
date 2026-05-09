<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Task Report" description="Execution quality, overdue items, and completion velocity.">
            <a href="{{ route('reports.export', 'tasks') }}" class="btn-secondary">{{ __('Export CSV') }}</a>
        </x-page-header>
    </x-slot>

    <div class="space-y-6">
        <div class="grid gap-4 md:grid-cols-3">
            <x-stat-card label="Completed" :value="$report['summary']['done_count']" accent="emerald" />
            <x-stat-card label="Overdue" :value="$report['summary']['overdue_count']" accent="rose" />
            <x-stat-card label="Avg Completion" :value="$report['summary']['avg_completion'].'%'" accent="sky" />
        </div>

        <div class="table-shell">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('Task') }}</th>
                        <th>{{ __('Project') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Priority') }}</th>
                        <th>{{ __('Due') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($report['items'] as $task)
                        <tr>
                            <td>{{ $task->title }}</td>
                            <td>{{ $task->project?->name }}</td>
                            <td>{{ __(str($task->status)->replace('_', ' ')->title()->toString()) }}</td>
                            <td>{{ __(str($task->priority)->title()->toString()) }}</td>
                            <td>{{ optional($task->due_date)->format('Y-m-d') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $report['items']->links() }}
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Project Report" description="Budget, completion, and schedule risk across active projects.">
            <a href="{{ route('reports.export', 'projects') }}" class="btn-secondary">{{ __('Export CSV') }}</a>
        </x-page-header>
    </x-slot>

    <div class="space-y-6">
        <div class="grid gap-4 md:grid-cols-4">
            <x-stat-card label="Budget Total" :value="'$'.number_format($report['summary']['budget_total'], 2)" />
            <x-stat-card label="Completed" :value="$report['summary']['completed_count']" accent="emerald" />
            <x-stat-card label="Late Projects" :value="$report['summary']['late_count']" accent="rose" />
            <x-stat-card label="Avg Progress" :value="$report['summary']['avg_progress'].'%'" accent="sky" />
        </div>

        <div class="table-shell">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('Project') }}</th>
                        <th>{{ __('Customer') }}</th>
                        <th>{{ __('Manager') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Progress') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($report['items'] as $project)
                        <tr>
                            <td>{{ $project->name }}</td>
                            <td>{{ $project->customer?->name }}</td>
                            <td>{{ $project->manager?->name }}</td>
                            <td>{{ __(str($project->status)->replace('_', ' ')->title()->toString()) }}</td>
                            <td>{{ $project->progress }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $report['items']->links() }}
    </div>
</x-app-layout>

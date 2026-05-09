<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Sales Report" description="Revenue and pipeline health across deals and closing stages.">
            <a href="{{ route('reports.export', 'sales') }}" class="btn-secondary">{{ __('Export CSV') }}</a>
        </x-page-header>
    </x-slot>

    <div class="space-y-6">
        <div class="grid gap-4 md:grid-cols-4">
            <x-stat-card label="Total Value" :value="'$'.number_format($report['summary']['total_value'], 2)" />
            <x-stat-card label="Won Value" :value="'$'.number_format($report['summary']['won_value'], 2)" accent="emerald" />
            <x-stat-card label="Open Value" :value="'$'.number_format($report['summary']['open_value'], 2)" accent="amber" />
            <x-stat-card label="Avg Probability" :value="$report['summary']['avg_probability'].'%'" accent="sky" />
        </div>

        <div class="table-shell">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('Deal') }}</th>
                        <th>{{ __('Stage') }}</th>
                        <th>{{ __('Owner') }}</th>
                        <th>{{ __('Value') }}</th>
                        <th>{{ __('Status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($report['items'] as $deal)
                        <tr>
                            <td>{{ $deal->title }}</td>
                            <td>{{ __($deal->stage?->name ?? 'Unknown') }}</td>
                            <td>{{ $deal->owner?->name }}</td>
                            <td>${{ number_format($deal->value, 2) }}</td>
                            <td>{{ __(str($deal->status)->title()->toString()) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $report['items']->links() }}
    </div>
</x-app-layout>

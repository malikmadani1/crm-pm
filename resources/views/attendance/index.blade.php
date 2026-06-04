<x-app-layout>
    <x-slot name="header">
        <x-page-header :title="__('Attendance')" :description="__('See when each employee checked in, checked out, and how many hours they worked each day.')" />
    </x-slot>

    <div class="space-y-6">
        <div class="grid gap-4 md:grid-cols-3">
            <x-stat-card :label="__('Recorded days')" :value="$summary['days_count']" :hint="__('Attendance days matching the current filters')" />
            <x-stat-card :label="__('Open work days')" :value="$summary['open_days_count']" :hint="__('Days with check-in but no check-out yet')" accent="amber" />
            <x-stat-card :label="__('Total work hours')" :value="$summary['worked_duration']" :hint="__('Total hours in the current results')" accent="sky" />
        </div>

        <form class="panel grid gap-4 lg:grid-cols-4">
            <select name="user_id">
                <option value="">{{ __('All employees') }}</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" @selected((string) request('user_id') === (string) $user->id)>{{ $user->name }}</option>
                @endforeach
            </select>

            <input type="date" name="from" value="{{ request('from') }}">
            <input type="date" name="to" value="{{ request('to') }}">

            <button class="btn-secondary">{{ __('Apply Filters') }}</button>
        </form>

        @if($records->count())
            <div class="table-shell">
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('Employee') }}</th>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Check-in Time') }}</th>
                            <th>{{ __('Check-out Time') }}</th>
                            <th>{{ __('Work Hours') }}</th>
                            <th>{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($records as $record)
                            <tr>
                                <td>{{ $record->user?->name }}</td>
                                <td>{{ $record->work_date?->format('Y-m-d') }}</td>
                                <td>{{ $record->checked_in_at?->format('H:i') ?: __('Not recorded') }}</td>
                                <td>{{ $record->checked_out_at?->format('H:i') ?: __('Not checked out yet') }}</td>
                                <td>{{ $record->workedDurationLabel() }}</td>
                                <td>
                                    @if($record->checked_in_at && ! $record->checked_out_at)
                                        <x-status-badge :value="__('At work')" color="amber" />
                                    @elseif($record->checked_in_at && $record->checked_out_at)
                                        <x-status-badge :value="__('Completed')" color="emerald" />
                                    @else
                                        <x-status-badge :value="__('Incomplete')" color="rose" />
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $records->links() }}
        @else
            <x-empty-state :title="__('No attendance records yet')" :message="__('Employee check-in and check-out records will appear here once the system is used.')" />
        @endif
    </div>
</x-app-layout>



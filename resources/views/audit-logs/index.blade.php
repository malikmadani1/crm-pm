<x-app-layout>
    <x-slot name="header">
        <x-page-header :title="__('Audit Log')" :description="__('Track every action inside the platform, who made it, and what changed.')" />
    </x-slot>

    <div class="space-y-6">
        <form class="panel grid gap-4 lg:grid-cols-5">
            <select name="user_id">
                <option value="">{{ __('All users') }}</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" @selected((string) request('user_id') === (string) $user->id)>{{ $user->name }}</option>
                @endforeach
            </select>

            <select name="module">
                <option value="">{{ __('All modules') }}</option>
                @foreach($modules as $module)
                    <option value="{{ $module }}" @selected(request('module') === $module)>{{ \App\Models\AuditLog::make(['module' => $module])->moduleLabel() }}</option>
                @endforeach
            </select>

            <select name="event">
                <option value="">{{ __('All events') }}</option>
                @foreach($events as $event)
                    <option value="{{ $event }}" @selected(request('event') === $event)>{{ \App\Models\AuditLog::make(['event' => $event])->eventLabel() }}</option>
                @endforeach
            </select>

            <input type="date" name="from" value="{{ request('from') }}">

            <button class="btn-secondary">{{ __('Apply Filters') }}</button>
        </form>

        @if($auditLogs->count())
            <div class="table-shell">
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('Event') }}</th>
                            <th>{{ __('Module') }}</th>
                            <th>{{ __('User') }}</th>
                            <th>{{ __('Date') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($auditLogs as $log)
                            <tr>
                                <td>
                                    <div class="font-semibold text-white">{{ $log->eventLabel() }}</div>
                                    @if($log->subjectLabel())
                                        <div class="mt-1 text-xs text-slate-400">
                                            {{ __('On') }}:
                                            @if($log->subjectUrl())
                                                <a href="{{ $log->subjectUrl() }}" class="text-cyan-300 hover:text-cyan-200">{{ $log->subjectLabel() }}</a>
                                            @else
                                                {{ $log->subjectLabel() }}
                                            @endif
                                        </div>
                                    @endif
                                    @if($log->summaryLines() !== [])
                                        <div class="mt-2 space-y-1 text-xs text-slate-400">
                                            @foreach($log->summaryLines(2) as $line)
                                                <div>{{ $line }}</div>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td>{{ $log->moduleLabel() }}</td>
                                <td>{{ $log->user?->name ?: __('System') }}</td>
                                <td>
                                    <div>{{ $log->created_at?->format('Y-m-d H:i') }}</div>
                                    <div class="mt-1 text-xs text-slate-500">{{ $log->created_at?->diffForHumans() }}</div>
                                </td>
                                <td class="text-right"><a href="{{ route('audit-logs.show', $log) }}" class="text-cyan-300">{{ __('Details') }}</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $auditLogs->links() }}
        @else
            <x-empty-state :title="__('No audit events yet')" :message="__('Important platform actions will appear here once work begins.')" />
        @endif
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Time Tracking" description="Review logged hours across projects, tasks, and team members.">
            <a href="{{ route('time-entries.create') }}" class="btn-primary">{{ __('Log Time') }}</a>
        </x-page-header>
    </x-slot>

    <div class="space-y-6">
        <form class="panel grid gap-4 lg:grid-cols-3">
            <select name="project_id">
                <option value="">{{ __('All projects') }}</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}" @selected((string) request('project_id') === (string) $project->id)>{{ $project->name }}</option>
                @endforeach
            </select>

            <select name="user_id">
                <option value="">{{ __('All users') }}</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" @selected((string) request('user_id') === (string) $user->id)>{{ $user->name }}</option>
                @endforeach
            </select>

            <button class="btn-secondary">{{ __('Apply Filters') }}</button>
        </form>

        @if($timeEntries->count())
            <div class="table-shell">
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('User') }}</th>
                            <th>{{ __('Project') }}</th>
                            <th>{{ __('Task') }}</th>
                            <th>{{ __('Hours') }}</th>
                            <th class="text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($timeEntries as $entry)
                            <tr>
                                <td>{{ $entry->user?->name }}</td>
                                <td>{{ $entry->project?->name }}</td>
                                <td>{{ $entry->task?->title ?: __('N/A') }}</td>
                                <td>{{ round($entry->minutes / 60, 1) }} {{ __('hours') }}</td>
                                <td class="text-right">
                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                        <a href="{{ route('time-entries.show', $entry) }}" class="rounded-xl border border-cyan-400/20 bg-cyan-500/10 px-3 py-2 text-xs font-semibold text-cyan-300 transition hover:bg-cyan-500/20">
                                            {{ __('Open') }}
                                        </a>
                                        @if(auth()->user()->hasPermissionTo('time_entries.update'))
                                            <a href="{{ route('time-entries.edit', $entry) }}" class="rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-xs font-semibold text-slate-200 transition hover:bg-white/10">
                                                {{ __('Edit') }}
                                            </a>
                                        @endif
                                        @if(auth()->user()->hasPermissionTo('time_entries.delete'))
                                            <x-delete-action
                                                :action="route('time-entries.destroy', $entry)"
                                                :title="__('Delete time entry')"
                                                :message="__('Are you sure you want to delete this time entry?')"
                                            />
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $timeEntries->links() }}
        @else
            <x-empty-state title="No time entries yet" message="Start logging delivery time to build performance and profitability insights." action="Log Time" :href="route('time-entries.create')" />
        @endif
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Projects" description="Plan, monitor, and deliver work tied directly to customers and team ownership.">
            <a href="{{ route('projects.create') }}" class="btn-primary">{{ __('New Project') }}</a>
        </x-page-header>
    </x-slot>

    <div class="space-y-6">
        <form class="panel grid gap-4 lg:grid-cols-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search projects') }}">

            <select name="status">
                <option value="">{{ __('All statuses') }}</option>
                @foreach(\App\Models\Project::STATUSES as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ __(str($status)->replace('_', ' ')->title()->toString()) }}</option>
                @endforeach
            </select>

            <select name="manager_id">
                <option value="">{{ __('All managers') }}</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" @selected((string) request('manager_id') === (string) $user->id)>{{ $user->name }}</option>
                @endforeach
            </select>

            <button class="btn-secondary">{{ __('Apply Filters') }}</button>
        </form>

        @if($projects->count())
            <div class="table-shell">
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('Project') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Manager') }}</th>
                            <th>{{ __('Progress') }}</th>
                            <th class="text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($projects as $project)
                            <tr>
                                <td>
                                    <div class="font-semibold text-white">{{ $project->name }}</div>
                                    <div class="text-xs text-slate-400">{{ collect([$project->customer?->name, $project->code])->filter()->join(' | ') }}</div>
                                </td>
                                <td><x-status-badge :value="$project->status" :color="config('crm_pm.labels.project_statuses.' . $project->status . '.color', 'slate')" /></td>
                                <td>{{ $project->manager?->name ?: __('N/A') }}</td>
                                <td>
                                    <div class="w-40">
                                        <div class="mb-2 flex justify-between text-xs text-slate-400">
                                            <span>{{ $project->progress }}%</span>
                                            <span>{{ $project->tasks_count }} {{ __('tasks') }}</span>
                                        </div>
                                        <div class="h-2 overflow-hidden rounded-full bg-slate-800">
                                            <div class="h-full rounded-full bg-cyan-400" style="width: {{ $project->progress }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-right">
                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                        <a href="{{ route('projects.show', $project) }}" class="rounded-xl border border-cyan-400/20 bg-cyan-500/10 px-3 py-2 text-xs font-semibold text-cyan-300 transition hover:bg-cyan-500/20">
                                            {{ __('Open') }}
                                        </a>
                                        @can('update', $project)
                                            <a href="{{ route('projects.edit', $project) }}" class="rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-xs font-semibold text-slate-200 transition hover:bg-white/10">
                                                {{ __('Edit') }}
                                            </a>
                                        @endcan
                                        @can('delete', $project)
                                            <x-delete-action
                                                :action="route('projects.destroy', $project)"
                                                :title="__('Delete project')"
                                                :message="__('Are you sure you want to delete this project?')"
                                            />
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $projects->links() }}
        @else
            <x-empty-state title="No projects yet" message="Projects will connect delivery execution with customers, team members, and time tracking." action="Create Project" :href="route('projects.create')" />
        @endif
    </div>
</x-app-layout>

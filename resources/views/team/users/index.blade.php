<x-app-layout>
    <x-slot name="header">
        <x-page-header :title="__('Users')" :description="__('Manage access, activation status, permissions, and operational responsibilities inside the platform.')">
            <a href="{{ route('users.create') }}" class="btn-primary">{{ __('Add User') }}</a>
        </x-page-header>
    </x-slot>

    <div class="space-y-6">
        <form class="panel grid gap-4 lg:grid-cols-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search name, email, code') }}">

            <select name="role">
                <option value="">{{ __('All roles') }}</option>
                @foreach($roles as $role)
                    <option value="{{ $role->slug }}" @selected(request('role') === $role->slug)>{{ __($role->name) }}</option>
                @endforeach
            </select>

            <select name="status">
                <option value="">{{ __('All statuses') }}</option>
                <option value="active" @selected(request('status') === 'active')>{{ __('Active') }}</option>
                <option value="inactive" @selected(request('status') === 'inactive')>{{ __('Inactive') }}</option>
            </select>

            <button class="btn-secondary">{{ __('Apply Filters') }}</button>
        </form>

        @if($users->count())
            <div class="table-shell">
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('Role') }}</th>
                            <th>{{ __('Workload') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th class="text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td>
                                    <div class="font-semibold text-white">{{ $user->name }}</div>
                                    <div class="text-xs text-slate-400">{{ $user->email }}</div>
                                </td>
                                <td>{{ collect($user->role_names)->map(fn ($roleName) => __($roleName))->join(', ') ?: __('No roles') }}</td>
                                <td class="text-xs text-slate-400">{{ $user->tasks_count }} {{ __('tasks') }} | {{ $user->projects_count }} {{ __('projects') }}</td>
                                <td><x-status-badge :value="$user->is_active ? 'active' : 'inactive'" :color="$user->is_active ? 'emerald' : 'rose'" /></td>
                                <td class="text-right">
                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                        <a href="{{ route('users.show', $user) }}" class="rounded-xl border border-cyan-400/20 bg-cyan-500/10 px-3 py-2 text-xs font-semibold text-cyan-300 transition hover:bg-cyan-500/20">
                                            {{ __('Open') }}
                                        </a>
                                        @can('update', $user)
                                            <a href="{{ route('users.edit', $user) }}" class="rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-xs font-semibold text-slate-200 transition hover:bg-white/10">
                                                {{ __('Edit') }}
                                            </a>
                                        @endcan
                                        @can('delete', $user)
                                            @if($user->isProtectedSuperAdmin())
                                                <span class="rounded-xl border border-amber-400/20 bg-amber-500/10 px-3 py-2 text-xs font-semibold text-amber-300">
                                                    {{ __('Protected admin user') }}
                                                </span>
                                            @elseif(auth()->id() === $user->id)
                                                <span class="rounded-xl border border-slate-400/20 bg-slate-500/10 px-3 py-2 text-xs font-semibold text-slate-300">
                                                    {{ __('Current session') }}
                                                </span>
                                            @else
                                                <x-delete-action
                                                    :action="route('users.destroy', $user)"
                                                    :title="__('Delete user')"
                                                    :message="__('Are you sure you want to delete this user?')"
                                                />
                                            @endif
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $users->links() }}
        @else
            <x-empty-state :title="__('No users found')" :message="__('Create the first user to start assigning work and permissions.')" :action="__('Add User')" :href="route('users.create')" />
        @endif
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Roles & Permissions" description="Control who can access each area of the CRM and PM workspace.">
            <a href="{{ route('roles.create') }}" class="btn-primary">{{ __('Create Role') }}</a>
        </x-page-header>
    </x-slot>

    @if($roles->count())
        <div class="table-shell">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('Role') }}</th>
                        <th>{{ __('Users') }}</th>
                        <th>{{ __('Permissions') }}</th>
                        <th class="text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($roles as $role)
                        <tr>
                            <td>
                                <div class="font-semibold text-white">{{ __($role->name) }}</div>
                                <div class="text-xs text-slate-400">{{ $role->slug }}</div>
                            </td>
                            <td>{{ $role->users_count }}</td>
                            <td>{{ $role->permissions_count }}</td>
                            <td class="text-right">
                                <div class="flex flex-wrap items-center justify-end gap-2">
                                    <a href="{{ route('roles.show', $role) }}" class="rounded-xl border border-cyan-400/20 bg-cyan-500/10 px-3 py-2 text-xs font-semibold text-cyan-300 transition hover:bg-cyan-500/20">
                                        {{ __('Open') }}
                                    </a>
                                    @can('update', $role)
                                        <a href="{{ route('roles.edit', $role) }}" class="rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-xs font-semibold text-slate-200 transition hover:bg-white/10">
                                            {{ __('Edit') }}
                                        </a>
                                    @endcan
                                    @can('delete', $role)
                                        <x-delete-action
                                            :action="route('roles.destroy', $role)"
                                            :title="__('Delete role')"
                                            :message="__('Are you sure you want to delete this role?')"
                                        />
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $roles->links() }}
    @else
        <x-empty-state title="No roles defined" message="Create roles to structure access across your organization." action="Create Role" :href="route('roles.create')" />
    @endif
</x-app-layout>

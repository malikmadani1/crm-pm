<x-app-layout>
    <x-slot name="header">
        <x-page-header :title="__($role->name)" :description="$role->description ?: 'Role definition and assigned permissions.'">
            @can('update', $role)
                <a href="{{ route('roles.edit', $role) }}" class="btn-secondary">{{ __('Edit') }}</a>
            @endcan
            @can('delete', $role)
                <x-delete-action
                    :action="route('roles.destroy', $role)"
                    :title="__('Delete role')"
                    :message="__('Are you sure you want to delete this role?')"
                />
            @endcan
        </x-page-header>
    </x-slot>

    <div class="grid gap-6 xl:grid-cols-[0.7fr_1.3fr]">
        <div class="panel">
            <dl class="space-y-4 text-sm">
                <div class="flex justify-between"><dt class="text-slate-400">{{ __('Name') }}</dt><dd>{{ __($role->name) }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-400">{{ __('Slug') }}</dt><dd>{{ $role->slug }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-400">{{ __('System Role') }}</dt><dd>{{ $role->is_system ? __('Yes') : __('No') }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-400">{{ __('Users Assigned') }}</dt><dd>{{ $role->users->count() }}</dd></div>
            </dl>
        </div>

        <div class="space-y-6">
            <div class="panel">
                <h3 class="text-lg font-semibold text-white">{{ __('Permissions') }}</h3>
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach($role->permissions as $permission)
                        <x-status-badge :value="__($permission->name)" color="cyan" />
                    @endforeach
                </div>
            </div>

            <div class="panel">
                <h3 class="text-lg font-semibold text-white">{{ __('Assigned Users') }}</h3>
                <div class="mt-4 space-y-3">
                    @forelse($role->users as $member)
                        <a href="{{ route('users.show', $member) }}" class="block rounded-2xl bg-white/5 px-4 py-4 hover:bg-white/10">
                            <div class="font-semibold text-white">{{ $member->name }}</div>
                            <div class="mt-1 text-xs text-slate-400">{{ $member->email }}</div>
                        </a>
                    @empty
                        <div class="text-sm text-slate-400">{{ __('No users assigned to this role.') }}</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

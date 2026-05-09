@props(['role' => null, 'permissions'])

@php
    $selectedPermissionIds = collect(old('permission_ids', $role?->permissions->pluck('id')->all() ?? []))
        ->map(fn ($id) => (int) $id)
        ->all();
@endphp

<div class="grid gap-6 lg:grid-cols-[0.65fr_1.35fr]">
    <div class="panel space-y-5">
        <div>
            <label class="mb-2 block text-sm text-slate-300">{{ __('Role Name') }}</label>
            <input type="text" name="name" value="{{ old('name', $role?->name) }}" required>
        </div>

        <div>
            <label class="mb-2 block text-sm text-slate-300">{{ __('Slug') }}</label>
            <input type="text" name="slug" value="{{ old('slug', $role?->slug) }}">
        </div>

        <div>
            <label class="mb-2 block text-sm text-slate-300">{{ __('Description') }}</label>
            <textarea name="description" rows="6">{{ old('description', $role?->description) }}</textarea>
        </div>
    </div>

    <div class="panel">
        <h3 class="text-lg font-semibold text-white">{{ __('Permissions') }}</h3>

        <div class="mt-5 space-y-4">
            @foreach($permissions as $module => $items)
                <div>
                    <div class="mb-3 text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">{{ __(str($module)->replace('_', ' ')->title()->toString()) }}</div>
                    <div class="grid gap-2 md:grid-cols-2">
                        @foreach($items as $permission)
                            <label class="flex items-center gap-3 rounded-2xl bg-white/5 px-4 py-3 text-sm text-slate-200">
                                <input type="checkbox" name="permission_ids[]" value="{{ $permission->id }}" class="app-checkbox h-4 w-4 rounded" @checked(in_array((int) $permission->id, $selectedPermissionIds, true))>
                                <span>{{ __($permission->name) }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

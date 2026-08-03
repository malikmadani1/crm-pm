@props(['role' => null, 'permissions'])

@php
    $selectedPermissionIds = collect(old('permission_ids', $role?->permissions->pluck('id')->all() ?? []))
        ->map(fn ($id) => (int) $id)
        ->all();

    $permissionSlugsById = $permissions
        ->flatten()
        ->mapWithKeys(fn ($permission) => [(int) $permission->id => $permission->slug]);

    $permissionIdsBySlug = $permissionSlugsById->flip();

    $permissionDependencies = $permissionSlugsById
        ->filter(fn (string $slug) => str_contains($slug, '.') && ! str_ends_with($slug, '.view'))
        ->mapWithKeys(function (string $slug, int $id) use ($permissionIdsBySlug) {
            [$module] = explode('.', $slug, 2);
            $viewSlug = "{$module}.view";

            return $permissionIdsBySlug->has($viewSlug)
                ? [$id => (int) $permissionIdsBySlug[$viewSlug]]
                : [];
        });
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

    <div class="panel" data-role-permissions-form data-permission-dependencies='@json($permissionDependencies)'>
        <h3 class="text-lg font-semibold text-white">{{ __('Permissions') }}</h3>

        <div class="mt-5 space-y-4">
            @foreach($permissions as $module => $items)
                <div>
                    <div class="mb-3 text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">{{ __(str($module)->replace('_', ' ')->title()->toString()) }}</div>
                    <div class="grid gap-2 md:grid-cols-2">
                        @foreach($items as $permission)
                            <label class="flex items-center gap-3 rounded-2xl bg-white/5 px-4 py-3 text-sm text-slate-200">
                                <input
                                    type="checkbox"
                                    name="permission_ids[]"
                                    value="{{ $permission->id }}"
                                    class="app-checkbox h-4 w-4 rounded"
                                    data-permission-checkbox
                                    data-permission-slug="{{ $permission->slug }}"
                                    @checked(in_array((int) $permission->id, $selectedPermissionIds, true))
                                >
                                <span>{{ __($permission->name) }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('[data-role-permissions-form]').forEach((form) => {
                    const dependencies = JSON.parse(form.dataset.permissionDependencies || '{}');
                    const checkboxes = new Map(
                        Array.from(form.querySelectorAll('[data-permission-checkbox]'))
                            .map((checkbox) => [checkbox.value, checkbox])
                    );

                    const applyDependency = (checkbox) => {
                        if (! checkbox.checked || ! dependencies[checkbox.value]) {
                            return;
                        }

                        const requiredCheckbox = checkboxes.get(String(dependencies[checkbox.value]));

                        if (requiredCheckbox) {
                            requiredCheckbox.checked = true;
                        }
                    };

                    const keepRequiredPermissions = (checkbox) => {
                        const isRequiredByCheckedPermission = Object.entries(dependencies).some(([permissionId, requiredId]) => {
                            const dependentCheckbox = checkboxes.get(permissionId);

                            return String(requiredId) === checkbox.value && dependentCheckbox?.checked;
                        });

                        if (isRequiredByCheckedPermission) {
                            checkbox.checked = true;
                        }
                    };

                    checkboxes.forEach(applyDependency);

                    checkboxes.forEach((checkbox) => {
                        checkbox.addEventListener('change', () => {
                            applyDependency(checkbox);
                            keepRequiredPermissions(checkbox);
                        });
                    });
                });
            });
        </script>
    @endpush
@endonce

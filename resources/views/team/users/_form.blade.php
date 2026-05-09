@props(['user' => null, 'roles', 'permissions'])

<div class="grid gap-6 lg:grid-cols-2">
    <div class="panel space-y-5">
        <h3 class="text-lg font-semibold text-white">{{ __('Identity') }}</h3>

        <div>
            <label class="mb-2 block text-sm text-slate-300">{{ __('Full Name') }}</label>
            <input type="text" name="name" value="{{ old('name', $user?->name) }}" required>
        </div>

        <div>
            <label class="mb-2 block text-sm text-slate-300">{{ __('Email') }}</label>
            <input type="email" name="email" value="{{ old('email', $user?->email) }}" required>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm text-slate-300">{{ __('Employee Code') }}</label>
                <input type="text" name="employee_code" value="{{ old('employee_code', $user?->employee_code) }}">
            </div>

            <div>
                <label class="mb-2 block text-sm text-slate-300">{{ __('Phone') }}</label>
                <input type="text" name="phone" value="{{ old('phone', $user?->phone) }}">
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm text-slate-300">{{ __('Job Title') }}</label>
                <input type="text" name="job_title" value="{{ old('job_title', $user?->job_title) }}">
            </div>

            <div>
                <label class="mb-2 block text-sm text-slate-300">{{ __('Timezone') }}</label>
                <input type="text" name="timezone" value="{{ old('timezone', $user?->timezone ?? 'Asia/Damascus') }}">
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm text-slate-300">{{ __('Password') }}</label>
                <input type="password" name="password" {{ $user ? '' : 'required' }}>
            </div>

            <div>
                <label class="mb-2 block text-sm text-slate-300">{{ __('Confirm Password') }}</label>
                <input type="password" name="password_confirmation" {{ $user ? '' : 'required' }}>
            </div>
        </div>

        <label class="flex items-center gap-3 rounded-2xl bg-white/5 px-4 py-3 text-sm text-slate-300">
            <input type="checkbox" name="is_active" value="1" class="app-checkbox h-4 w-4 rounded" @checked(old('is_active', $user?->is_active ?? true))>
            {{ __('Account is active') }}
        </label>
    </div>

    <div class="space-y-6">
        <div class="panel">
            <h3 class="text-lg font-semibold text-white">{{ __('Roles') }}</h3>
            <div class="mt-4 space-y-3">
                @foreach($roles as $role)
                    <label class="flex items-center gap-3 rounded-2xl bg-white/5 px-4 py-3 text-sm text-slate-200">
                        <input type="checkbox" name="role_ids[]" value="{{ $role->id }}" class="app-checkbox h-4 w-4 rounded" @checked(in_array($role->id, old('role_ids', $user?->roles->pluck('id')->all() ?? [])))>
                        <span>{{ __($role->name) }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="panel">
            <h3 class="text-lg font-semibold text-white">{{ __('Direct Permissions') }}</h3>
            <div class="mt-4 space-y-4">
                @foreach($permissions as $module => $items)
                    <div>
                        <div class="mb-2 text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">{{ __(str($module)->replace('_', ' ')->title()->toString()) }}</div>
                        <div class="grid gap-2 md:grid-cols-2">
                            @foreach($items as $permission)
                                <label class="flex items-center gap-3 rounded-2xl bg-white/5 px-4 py-3 text-sm text-slate-200">
                                    <input type="checkbox" name="permission_ids[]" value="{{ $permission->id }}" class="app-checkbox h-4 w-4 rounded" @checked(in_array($permission->id, old('permission_ids', $user?->directPermissions->pluck('id')->all() ?? [])))>
                                    <span>{{ __($permission->name) }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

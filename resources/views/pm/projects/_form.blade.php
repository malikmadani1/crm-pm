@props(['project' => null, 'customers', 'users'])

<div class="grid gap-6 lg:grid-cols-2">
    <div class="panel space-y-5">
        <div>
            <label class="mb-2 block text-sm text-slate-300">{{ __('Project Name') }}</label>
            <input type="text" name="name" value="{{ old('name', $project?->name) }}" required>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm text-slate-300">{{ __('Code') }}</label>
                <input type="text" name="code" value="{{ old('code', $project?->code) }}">
            </div>

            <div>
                <label class="mb-2 block text-sm text-slate-300">{{ __('Customer') }}</label>
                <select name="customer_id">
                    <option value="">{{ __('No linked customer') }}</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" @selected((string) old('customer_id', $project?->customer_id) === (string) $customer->id)>{{ $customer->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="mb-2 block text-sm text-slate-300">{{ __('Description') }}</label>
            <textarea name="description" rows="8">{{ old('description', $project?->description) }}</textarea>
        </div>
    </div>

    <div class="panel space-y-5">
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm text-slate-300">{{ __('Manager') }}</label>
                <select name="manager_id">
                    <option value="">{{ __('Select manager') }}</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" @selected((string) old('manager_id', $project?->manager_id) === (string) $user->id)>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm text-slate-300">{{ __('Budget') }}</label>
                <input type="number" step="0.01" name="budget" value="{{ old('budget', $project?->budget) }}">
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div>
                <label class="mb-2 block text-sm text-slate-300">{{ __('Status') }}</label>
                <select name="status">
                    @foreach(\App\Models\Project::STATUSES as $status)
                        <option value="{{ $status }}" @selected(old('status', $project?->status ?? 'in_progress') === $status)>{{ __(str($status)->replace('_', ' ')->title()->toString()) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm text-slate-300">{{ __('Priority') }}</label>
                <select name="priority">
                    @foreach(\App\Models\Project::PRIORITIES as $priority)
                        <option value="{{ $priority }}" @selected(old('priority', $project?->priority ?? 'medium') === $priority)>{{ __(str($priority)->title()->toString()) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm text-slate-300">{{ __('Start Date') }}</label>
                <input type="date" name="start_date" value="{{ old('start_date', optional($project?->start_date)->format('Y-m-d')) }}">
            </div>
        </div>

        <div>
            <label class="mb-2 block text-sm text-slate-300">{{ __('Due Date') }}</label>
            <input type="date" name="due_date" value="{{ old('due_date', optional($project?->due_date)->format('Y-m-d')) }}">
        </div>

        <div>
            <label class="mb-3 block text-sm text-slate-300">{{ __('Members') }}</label>
            <div class="grid gap-2 md:grid-cols-2">
                @foreach($users as $user)
                    <label class="flex items-center gap-3 rounded-2xl bg-white/5 px-4 py-3 text-sm text-slate-200">
                        <input type="checkbox" name="member_ids[]" value="{{ $user->id }}" class="app-checkbox h-4 w-4 rounded" @checked(in_array($user->id, old('member_ids', $project?->members->pluck('id')->all() ?? [])))>
                        <span>{{ $user->name }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    </div>
</div>

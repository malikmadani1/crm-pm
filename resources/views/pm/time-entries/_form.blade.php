@props(['timeEntry' => null, 'projects', 'tasks', 'users'])

<div class="grid gap-6 lg:grid-cols-2">
    <div class="panel space-y-5">
        <div>
            <label class="mb-2 block text-sm text-slate-300">{{ __('Project') }}</label>
            <select name="project_id">
                @foreach($projects as $project)
                    <option value="{{ $project->id }}" @selected((string) old('project_id', $timeEntry?->project_id) === (string) $project->id)>{{ $project->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="mb-2 block text-sm text-slate-300">{{ __('Task') }}</label>
            <select name="task_id">
                <option value="">{{ __('No task') }}</option>
                @foreach($tasks as $task)
                    <option value="{{ $task->id }}" @selected((string) old('task_id', $timeEntry?->task_id) === (string) $task->id)>{{ $task->title }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="mb-2 block text-sm text-slate-300">{{ __('User') }}</label>
            <select name="user_id">
                <option value="">{{ __('Current user') }}</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" @selected((string) old('user_id', $timeEntry?->user_id) === (string) $user->id)>{{ $user->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="panel space-y-5">
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm text-slate-300">{{ __('Started At') }}</label>
                <input type="datetime-local" name="started_at" value="{{ old('started_at', optional($timeEntry?->started_at)->format('Y-m-d\\TH:i')) }}">
            </div>

            <div>
                <label class="mb-2 block text-sm text-slate-300">{{ __('Ended At') }}</label>
                <input type="datetime-local" name="ended_at" value="{{ old('ended_at', optional($timeEntry?->ended_at)->format('Y-m-d\\TH:i')) }}">
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm text-slate-300">{{ __('Minutes') }}</label>
                <input type="number" name="minutes" value="{{ old('minutes', $timeEntry?->minutes) }}">
            </div>

            <label class="flex items-center gap-3 rounded-2xl bg-white/5 px-4 py-3 text-sm text-slate-300">
                <input type="checkbox" name="billable" value="1" class="h-4 w-4 rounded border-white/20 bg-transparent" @checked(old('billable', $timeEntry?->billable))>
                {{ __('Billable') }}
            </label>
        </div>

        <div>
            <label class="mb-2 block text-sm text-slate-300">{{ __('Description') }}</label>
            <textarea name="description" rows="6">{{ old('description', $timeEntry?->description) }}</textarea>
        </div>
    </div>
</div>

@props(['task' => null, 'projects', 'users', 'tags', 'parentTasks'])

<div class="grid gap-6 lg:grid-cols-2">
    <div class="panel space-y-5">
        <div>
            <label class="mb-2 block text-sm text-slate-300">{{ __('Task Title') }}</label>
            <input type="text" name="title" value="{{ old('title', $task?->title) }}" required>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm text-slate-300">{{ __('Project') }}</label>
                <select name="project_id">
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}" @selected((string) old('project_id', request('project_id', $task?->project_id)) === (string) $project->id)>{{ $project->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm text-slate-300">{{ __('Parent Task') }}</label>
                <select name="parent_id">
                    <option value="">{{ __('No parent') }}</option>
                    @foreach($parentTasks as $parentTask)
                        <option value="{{ $parentTask->id }}" @selected((string) old('parent_id', $task?->parent_id) === (string) $parentTask->id)>{{ $parentTask->title }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="mb-2 block text-sm text-slate-300">{{ __('Description') }}</label>
            <textarea name="description" rows="8">{{ old('description', $task?->description) }}</textarea>
        </div>
    </div>

    <div class="panel space-y-5">
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm text-slate-300">{{ __('Status') }}</label>
                <select name="status">
                    @foreach(\App\Models\Task::STATUSES as $status)
                        <option value="{{ $status }}" @selected(old('status', $task?->status ?? 'todo') === $status)>{{ __(str($status)->replace('_', ' ')->title()->toString()) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm text-slate-300">{{ __('Priority') }}</label>
                <select name="priority">
                    @foreach(\App\Models\Task::PRIORITIES as $priority)
                        <option value="{{ $priority }}" @selected(old('priority', $task?->priority ?? 'medium') === $priority)>{{ __(str($priority)->title()->toString()) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm text-slate-300">{{ __('Start Date') }}</label>
                <input type="date" name="start_date" value="{{ old('start_date', optional($task?->start_date)->format('Y-m-d')) }}">
            </div>

            <div>
                <label class="mb-2 block text-sm text-slate-300">{{ __('Due Date') }}</label>
                <input type="date" name="due_date" value="{{ old('due_date', optional($task?->due_date)->format('Y-m-d')) }}">
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div>
                <label class="mb-2 block text-sm text-slate-300">{{ __('Estimated Hours') }}</label>
                <input type="number" step="0.01" name="estimated_hours" value="{{ old('estimated_hours', $task?->estimated_hours) }}">
            </div>

            <div>
                <label class="mb-2 block text-sm text-slate-300">{{ __('Actual Hours') }}</label>
                <input type="number" step="0.01" name="actual_hours" value="{{ old('actual_hours', $task?->actual_hours) }}">
            </div>

            <div>
                <label class="mb-2 block text-sm text-slate-300">{{ __('Completion %') }}</label>
                <input type="number" name="completion_percentage" value="{{ old('completion_percentage', $task?->completion_percentage ?? 0) }}">
            </div>
        </div>

        <div>
            <label class="mb-3 block text-sm text-slate-300">{{ __('Assignees') }}</label>
            <div class="grid gap-2 md:grid-cols-2">
                @foreach($users as $user)
                    <label class="flex items-center gap-3 rounded-2xl bg-white/5 px-4 py-3 text-sm text-slate-200">
                        <input type="checkbox" name="assignee_ids[]" value="{{ $user->id }}" class="h-4 w-4 rounded border-white/20 bg-transparent" @checked(in_array($user->id, old('assignee_ids', $task?->assignees->pluck('id')->all() ?? [])))>
                        {{ $user->name }}
                    </label>
                @endforeach
            </div>
        </div>

        <div>
            <label class="mb-3 block text-sm text-slate-300">{{ __('Tags') }}</label>
            <div class="grid gap-2 md:grid-cols-2">
                @foreach($tags as $tag)
                    <label class="flex items-center gap-3 rounded-2xl bg-white/5 px-4 py-3 text-sm text-slate-200">
                        <input type="checkbox" name="tag_ids[]" value="{{ $tag->id }}" class="h-4 w-4 rounded border-white/20 bg-transparent" @checked(in_array($tag->id, old('tag_ids', $task?->tags->pluck('id')->all() ?? [])))>
                        {{ $tag->name }}
                    </label>
                @endforeach
            </div>
        </div>
    </div>
</div>

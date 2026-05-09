<?php

namespace App\Http\Controllers\PM;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Project;
use App\Models\Tag;
use App\Models\Task;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\TaskTimerService;
use App\Services\TaskWorkflowService;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Task::class);

        $projects = Project::query()->orderBy('name')->get(['id', 'name']);
        $users = User::query()->active()->orderBy('name')->get(['id', 'name']);

        $tasks = Task::query()
            ->with(['project', 'assignees', 'tags'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));
                $query->where('title', 'like', "%{$search}%");
            })
            ->when($request->filled('project_id'), fn ($query) => $query->where('project_id', $request->integer('project_id')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('priority'), fn ($query) => $query->where('priority', $request->string('priority')))
            ->when($request->filled('assignee_id'), fn ($query) => $query->whereHas('assignees', fn ($assigneeQuery) => $assigneeQuery->where('users.id', $request->integer('assignee_id'))))
            ->when(! $request->user()->isAdmin() && ! $request->user()->hasRole('manager'), fn ($query) => $query->whereHas('assignees', fn ($assigneeQuery) => $assigneeQuery->where('users.id', $request->user()->id)))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('pm.tasks.index', compact('tasks', 'projects', 'users'));
    }

    public function create()
    {
        $this->authorize('create', Task::class);

        return view('pm.tasks.create', [
            'projects' => Project::query()->orderBy('name')->get(['id', 'name']),
            'users' => User::query()->active()->orderBy('name')->get(['id', 'name']),
            'tags' => Tag::query()->orderBy('name')->get(['id', 'name']),
            'parentTasks' => Task::query()->orderBy('title')->get(['id', 'title']),
        ]);
    }

    public function store(StoreTaskRequest $request, TaskWorkflowService $taskWorkflowService)
    {
        $data = $this->normalizeTaskPayload($request->validated());
        $assigneeIds = $data['assignee_ids'] ?? [];
        $tagIds = $data['tag_ids'] ?? [];

        unset($data['assignee_ids'], $data['tag_ids']);

        $data['created_by'] = $request->user()->id;
        $data['position'] = Task::query()
            ->where('project_id', $data['project_id'])
            ->where('status', $data['status'])
            ->max('position') + 1;

        $task = Task::query()->create($data);
        $taskWorkflowService->syncAssignments($task, $assigneeIds);
        $task->tags()->sync($tagIds);
        $taskWorkflowService->storeTaskLog($task, 'task_created', null, $task->load('assignees', 'tags')->toArray(), __('Task created.'));
        $taskWorkflowService->syncProjectProgress($task->project);

        return redirect()->route('tasks.show', $task)->with('success', __('Task created successfully.'));
    }

    public function show(Request $request, Task $task, TaskTimerService $taskTimerService)
    {
        $this->authorize('view', $task);

        $task->load([
            'project',
            'creator',
            'assignees',
            'comments.user',
            'logs.user',
            'timeEntries.user',
            'tags',
            'subtasks.assignees',
        ]);

        $activeTimer = $taskTimerService->activeEntryFor($request->user());
        $taskTrackedMinutes = (int) $task->timeEntries->sum('minutes');
        $taskUserTrackedMinutes = (int) $task->timeEntries->where('user_id', $request->user()->id)->sum('minutes');

        $viewData = compact('task', 'activeTimer', 'taskTrackedMinutes', 'taskUserTrackedMinutes');

        if ($request->boolean('panel')) {
            return view('pm.tasks.partials.drawer', $viewData);
        }

        return view('pm.tasks.show', $viewData);
    }

    public function edit(Task $task)
    {
        $this->authorize('update', $task);

        return view('pm.tasks.edit', [
            'task' => $task->load('assignees', 'tags'),
            'projects' => Project::query()->orderBy('name')->get(['id', 'name']),
            'users' => User::query()->active()->orderBy('name')->get(['id', 'name']),
            'tags' => Tag::query()->orderBy('name')->get(['id', 'name']),
            'parentTasks' => Task::query()->where('id', '!=', $task->id)->orderBy('title')->get(['id', 'title']),
        ]);
    }

    public function update(UpdateTaskRequest $request, Task $task, TaskWorkflowService $taskWorkflowService)
    {
        $this->authorize('update', $task);

        $oldValues = $task->load('assignees', 'tags')->toArray();
        $data = $this->normalizeTaskPayload($request->validated());
        $assigneeIds = $data['assignee_ids'] ?? [];
        $tagIds = $data['tag_ids'] ?? [];

        unset($data['assignee_ids'], $data['tag_ids']);

        $task->update($data);
        $taskWorkflowService->syncAssignments($task, $assigneeIds);
        $task->tags()->sync($tagIds);
        $taskWorkflowService->storeTaskLog($task, 'task_updated', $oldValues, $task->fresh()->load('assignees', 'tags')->toArray(), __('Task updated.'));
        $taskWorkflowService->syncProjectProgress($task->project);

        return redirect()->route('tasks.show', $task)->with('success', __('Task updated successfully.'));
    }

    public function destroy(Task $task, AuditLogService $auditLogService)
    {
        $this->authorize('delete', $task);

        $auditLogService->record(
            module: 'tasks',
            event: 'task_deleted',
            auditable: $task,
            oldValues: $task->toArray(),
            description: __('Task :title deleted.', ['title' => $task->title]),
        );

        $project = $task->project;
        $task->delete();

        if ($project) {
            app(TaskWorkflowService::class)->syncProjectProgress($project);
        }

        return redirect()->route('tasks.index')->with('success', __('Task deleted successfully.'));
    }

    private function normalizeTaskPayload(array $data): array
    {
        $data['actual_hours'] = isset($data['actual_hours']) ? (float) $data['actual_hours'] : 0;
        $data['completion_percentage'] = isset($data['completion_percentage']) ? (int) $data['completion_percentage'] : 0;
        $data['parent_id'] = $data['parent_id'] ?: null;
        $data['description'] = $data['description'] ?: null;
        $data['start_date'] = $data['start_date'] ?: null;
        $data['due_date'] = $data['due_date'] ?: null;
        $data['estimated_hours'] = $data['estimated_hours'] === null || $data['estimated_hours'] === '' ? null : (float) $data['estimated_hours'];
        $data['assignee_ids'] = array_values($data['assignee_ids'] ?? []);
        $data['tag_ids'] = array_values($data['tag_ids'] ?? []);

        return $data;
    }
}

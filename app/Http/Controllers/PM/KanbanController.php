<?php

namespace App\Http\Controllers\PM;

use App\Http\Controllers\Controller;
use App\Http\Requests\MoveTaskRequest;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskWorkflowService;
use Illuminate\Http\Request;

class KanbanController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->hasPermissionTo('tasks.view'), 403);

        $selectedProjectId = $request->integer('project_id');
        $selectedUserId = $request->integer('user_id');
        $selectedPriority = $request->string('priority')->toString();

        $tasks = Task::query()
            ->with(['project', 'assignees', 'tags'])
            ->when($selectedProjectId, fn ($query) => $query->where('project_id', $selectedProjectId))
            ->when($selectedPriority, fn ($query) => $query->where('priority', $selectedPriority))
            ->when($selectedUserId, fn ($query) => $query->whereHas('assignees', fn ($assigneeQuery) => $assigneeQuery->where('users.id', $selectedUserId)))
            ->orderBy('position')
            ->get()
            ->groupBy('status');

        $projects = Project::query()->orderBy('name')->get(['id', 'name']);
        $users = User::query()->active()->orderBy('name')->get(['id', 'name']);

        return view('pm.kanban.index', compact('tasks', 'projects', 'users', 'selectedProjectId', 'selectedUserId', 'selectedPriority'));
    }

    public function move(MoveTaskRequest $request, Task $task, TaskWorkflowService $taskWorkflowService)
    {
        $taskWorkflowService->move($task, $request->string('status')->toString());

        return response()->json([
            'message' => __('Task moved successfully.'),
        ]);
    }
}

<?php

namespace App\Http\Controllers\PM;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskCommentRequest;
use App\Models\Task;
use App\Models\TaskComment;
use App\Services\AuditLogService;
use App\Services\TaskWorkflowService;
use Illuminate\Http\Request;

class TaskCommentController extends Controller
{
    public function store(StoreTaskCommentRequest $request, Task $task, TaskWorkflowService $taskWorkflowService)
    {
        $taskWorkflowService->addComment($task, $request->string('body')->toString(), $request->user());

        return back()->with('success', __('Comment added successfully.'));
    }

    public function destroy(Request $request, Task $task, TaskComment $comment, AuditLogService $auditLogService)
    {
        abort_unless($comment->task_id === $task->id, 404);
        abort_unless($comment->user_id === $request->user()->id || $request->user()->hasRole('manager'), 403);

        $auditLogService->record(
            module: 'tasks',
            event: 'comment_deleted',
            auditable: $task,
            oldValues: ['comment' => $comment->toArray()],
            description: __('Comment #:id removed from task :title.', ['id' => $comment->id, 'title' => $task->title]),
        );

        $comment->delete();

        return back()->with('success', __('Comment deleted successfully.'));
    }
}

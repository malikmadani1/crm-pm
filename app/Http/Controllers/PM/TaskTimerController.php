<?php

namespace App\Http\Controllers\PM;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Services\TaskTimerService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TaskTimerController extends Controller
{
    public function start(Request $request, Task $task, TaskTimerService $taskTimerService)
    {
        $this->authorize('view', $task);

        try {
            $taskTimerService->start($task, $request->user());
        } catch (ValidationException $exception) {
            return $this->timerResponse($request, false, $exception->validator->errors()->first());
        }

        return $this->timerResponse($request, true, __('Task timer started successfully.'));
    }

    public function stop(Request $request, Task $task, TaskTimerService $taskTimerService)
    {
        $this->authorize('view', $task);

        try {
            $taskTimerService->stop($task, $request->user());
        } catch (ValidationException $exception) {
            return $this->timerResponse($request, false, $exception->validator->errors()->first());
        }

        return $this->timerResponse($request, true, __('Task timer stopped successfully.'));
    }

    private function timerResponse(Request $request, bool $success, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => $success,
                'message' => $message,
            ], $success ? 200 : 422);
        }

        return back()->with($success ? 'success' : 'error', $message);
    }
}

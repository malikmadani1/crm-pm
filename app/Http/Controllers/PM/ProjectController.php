<?php

namespace App\Http\Controllers\PM;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Customer;
use App\Models\Project;
use App\Models\User;
use App\Notifications\ProjectAssignedNotification;
use App\Notifications\ProjectStatusChangedNotification;
use App\Services\AuditLogService;
use App\Services\TaskWorkflowService;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Project::class);

        $customers = Customer::query()->orderBy('name')->get(['id', 'name']);
        $users = User::query()->active()->orderBy('name')->get(['id', 'name']);

        $projects = Project::query()
            ->with(['customer', 'manager'])
            ->withCount(['tasks', 'members'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));

                $query->where(function ($nested) use ($search) {
                    $nested->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('customer_id'), fn ($query) => $query->where('customer_id', $request->integer('customer_id')))
            ->when($request->filled('manager_id'), fn ($query) => $query->where('manager_id', $request->integer('manager_id')))
            ->when(! $request->user()->isAdmin() && ! $request->user()->hasRole('manager'), function ($query) use ($request) {
                $query->where(function ($nested) use ($request) {
                    $nested->where('manager_id', $request->user()->id)
                        ->orWhereHas('members', fn ($memberQuery) => $memberQuery->where('users.id', $request->user()->id));
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pm.projects.index', compact('projects', 'customers', 'users'));
    }

    public function create()
    {
        $this->authorize('create', Project::class);

        return view('pm.projects.create', [
            'customers' => Customer::query()->orderBy('name')->get(['id', 'name']),
            'users' => User::query()->active()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StoreProjectRequest $request, AuditLogService $auditLogService)
    {
        $data = $request->validated();
        $memberIds = collect($data['member_ids'] ?? [])->map(fn ($id) => (int) $id);
        unset($data['member_ids']);

        $project = Project::query()->create($data);

        if ($project->manager_id) {
            $memberIds->push((int) $project->manager_id);
        }

        $syncedMemberIds = $memberIds->filter()->unique()->values();

        $project->members()->sync(
            $syncedMemberIds->mapWithKeys(fn ($id) => [$id => ['role' => $id == $project->manager_id ? 'manager' : 'member']])->all()
        );

        if ($syncedMemberIds->isNotEmpty()) {
            User::query()
                ->whereIn('id', $syncedMemberIds->all())
                ->get()
                ->each(fn (User $user) => $user->notify(new ProjectAssignedNotification($project)));
        }

        $auditLogService->record(
            module: 'projects',
            event: 'project_created',
            auditable: $project,
            newValues: $project->load('members')->toArray(),
            description: __('Project :name created.', ['name' => $project->name]),
        );

        return redirect()->route('projects.show', $project)->with('success', __('Project created successfully.'));
    }

    public function show(Project $project)
    {
        $this->authorize('view', $project);

        $project->load([
            'customer',
            'manager',
            'members',
            'tasks.assignees',
            'timeEntries.user',
            'attachments',
        ]);

        $recentTasks = $project->tasks()->with('assignees')->latest()->limit(8)->get();

        return view('pm.projects.show', compact('project', 'recentTasks'));
    }

    public function edit(Project $project)
    {
        $this->authorize('update', $project);

        return view('pm.projects.edit', [
            'project' => $project->load('members'),
            'customers' => Customer::query()->orderBy('name')->get(['id', 'name']),
            'users' => User::query()->active()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpdateProjectRequest $request, Project $project, AuditLogService $auditLogService, TaskWorkflowService $taskWorkflowService)
    {
        $this->authorize('update', $project);

        $oldValues = $project->load('members')->toArray();
        $existingMemberIds = $project->members->pluck('id')->map(fn ($id) => (int) $id);
        $oldStatus = $project->status;
        $data = $request->validated();
        $memberIds = collect($data['member_ids'] ?? [])->map(fn ($id) => (int) $id);
        unset($data['member_ids']);

        $project->update($data);

        if ($project->manager_id) {
            $memberIds->push((int) $project->manager_id);
        }

        $syncedMemberIds = $memberIds->filter()->unique()->values();
        $newlyAssignedMemberIds = $syncedMemberIds->diff($existingMemberIds)->values();

        $project->members()->sync(
            $syncedMemberIds->mapWithKeys(fn ($id) => [$id => ['role' => $id == $project->manager_id ? 'manager' : 'member']])->all()
        );

        if ($newlyAssignedMemberIds->isNotEmpty()) {
            User::query()
                ->whereIn('id', $newlyAssignedMemberIds->all())
                ->get()
                ->each(fn (User $user) => $user->notify(new ProjectAssignedNotification($project)));
        }

        $taskWorkflowService->syncProjectProgress($project->fresh());

        if ($oldStatus !== $project->status) {
            $project->members()->get()->each(fn (User $user) => $user->notify(
                new ProjectStatusChangedNotification($project, $oldStatus, $project->status)
            ));
        }

        $auditLogService->record(
            module: 'projects',
            event: 'project_updated',
            auditable: $project,
            oldValues: $oldValues,
            newValues: $project->fresh()->load('members')->toArray(),
            description: __('Project :name updated.', ['name' => $project->name]),
        );

        return redirect()->route('projects.show', $project)->with('success', __('Project updated successfully.'));
    }

    public function destroy(Project $project, AuditLogService $auditLogService)
    {
        $this->authorize('delete', $project);

        $auditLogService->record(
            module: 'projects',
            event: 'project_deleted',
            auditable: $project,
            oldValues: $project->toArray(),
            description: __('Project :name deleted.', ['name' => $project->name]),
        );

        $project->delete();

        return redirect()->route('projects.index')->with('success', __('Project deleted successfully.'));
    }
}

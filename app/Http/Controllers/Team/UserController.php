<?php

namespace App\Http\Controllers\Team;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $users = User::query()
            ->with('roles')
            ->withCount(['tasks', 'projects'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));

                $query->where(function ($nested) use ($search) {
                    $nested->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('employee_code', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('role'), function ($query) use ($request) {
                $query->whereHas('roles', fn ($roleQuery) => $roleQuery->where('slug', $request->string('role')));
            })
            ->when($request->filled('status'), fn ($query) => $query->where('is_active', $request->string('status') === 'active'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $roles = Role::query()->orderBy('name')->get();

        return view('team.users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $this->authorize('create', User::class);

        return view('team.users.create', [
            'roles' => Role::query()->orderBy('name')->get(),
            'permissions' => Permission::query()->orderBy('module')->orderBy('name')->get()->groupBy('module'),
        ]);
    }

    public function store(StoreUserRequest $request, AuditLogService $auditLogService)
    {
        $data = $request->validated();
        $roleIds = $data['role_ids'];
        $permissionIds = $data['permission_ids'] ?? [];

        unset($data['role_ids'], $data['permission_ids']);

        $user = User::query()->create($data);
        $user->syncRolesByIds($roleIds);
        $user->syncPermissionsByIds($permissionIds);

        $auditLogService->record(
            module: 'team',
            event: 'user_created',
            auditable: $user,
            newValues: $user->load('roles', 'directPermissions')->toArray(),
            description: __('User :name created.', ['name' => $user->name]),
        );

        return redirect()->route('users.show', $user)->with('success', __('User created successfully.'));
    }

    public function show(User $user)
    {
        $this->authorize('view', $user);

        $user->load(['roles', 'directPermissions', 'tasks.project', 'projects', 'timeEntries.project'])
            ->loadCount([
                'tasks as completed_tasks_count' => fn ($query) => $query->where('status', 'done'),
                'projects as active_projects_count',
            ]);

        $recentActivity = $user->auditLogs()->latest('created_at')->limit(12)->get();

        return view('team.users.show', compact('user', 'recentActivity'));
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);

        return view('team.users.edit', [
            'user' => $user->load('roles', 'directPermissions'),
            'roles' => Role::query()->orderBy('name')->get(),
            'permissions' => Permission::query()->orderBy('module')->orderBy('name')->get()->groupBy('module'),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user, AuditLogService $auditLogService)
    {
        $oldValues = $user->load('roles', 'directPermissions')->toArray();
        $data = $request->validated();
        $roleIds = $data['role_ids'];
        $permissionIds = $data['permission_ids'] ?? [];

        if (empty($data['password'])) {
            unset($data['password']);
        }

        unset($data['role_ids'], $data['permission_ids']);

        $user->update($data);
        $user->syncRolesByIds($roleIds);
        $user->syncPermissionsByIds($permissionIds);

        $auditLogService->record(
            module: 'team',
            event: 'user_updated',
            auditable: $user,
            oldValues: $oldValues,
            newValues: $user->fresh()->load('roles', 'directPermissions')->toArray(),
            description: __('User :name updated.', ['name' => $user->name]),
        );

        return redirect()->route('users.show', $user)->with('success', __('User updated successfully.'));
    }

    public function destroy(User $user, AuditLogService $auditLogService)
    {
        $this->authorize('delete', $user);

        if (request()->user()?->is($user)) {
            return back()->with('error', __('You cannot delete the account that is currently signed in.'));
        }

        if ($user->isProtectedSuperAdmin()) {
            return back()->with('error', __('You cannot delete this user because it is the protected super admin account.'));
        }

        $auditLogService->record(
            module: 'team',
            event: 'user_deleted',
            auditable: $user,
            oldValues: $user->load('roles', 'directPermissions')->toArray(),
            description: __('User :name deleted.', ['name' => $user->name]),
        );

        $user->delete();

        return redirect()->route('users.index')->with('success', __('User deleted successfully.'));
    }
}

<?php

namespace App\Http\Controllers\Team;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Services\AuditLogService;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Role::class);

        $roles = Role::query()
            ->withCount(['users', 'permissions'])
            ->orderBy('name')
            ->paginate(15);

        return view('team.roles.index', compact('roles'));
    }

    public function create()
    {
        $this->authorize('create', Role::class);

        $permissions = Permission::query()->orderBy('module')->orderBy('name')->get()->groupBy('module');

        return view('team.roles.create', compact('permissions'));
    }

    public function store(StoreRoleRequest $request, AuditLogService $auditLogService)
    {
        $data = $request->validated();
        $permissionIds = $this->withRequiredPermissionIds($data['permission_ids'] ?? []);

        unset($data['permission_ids']);

        $role = Role::query()->create([
            ...$data,
            'slug' => $data['slug'] ?? Str::slug($data['name']),
            'guard_name' => 'web',
        ]);

        $role->permissions()->sync($permissionIds);

        $auditLogService->record(
            module: 'team',
            event: 'role_created',
            auditable: $role,
            newValues: $role->load('permissions')->toArray(),
            description: __('Role :name created.', ['name' => __($role->name)]),
        );

        return redirect()->route('roles.show', $role)->with('success', __('Role created successfully.'));
    }

    public function show(Role $role)
    {
        $this->authorize('view', $role);

        $role->load('permissions', 'users');

        return view('team.roles.show', compact('role'));
    }

    public function edit(Role $role)
    {
        $this->authorize('update', $role);

        $role->load('permissions');
        $permissions = Permission::query()->orderBy('module')->orderBy('name')->get()->groupBy('module');

        return view('team.roles.edit', compact('role', 'permissions'));
    }

    public function update(UpdateRoleRequest $request, Role $role, AuditLogService $auditLogService)
    {
        $oldValues = $role->load('permissions')->toArray();
        $data = $request->validated();
        $permissionIds = $this->withRequiredPermissionIds($data['permission_ids'] ?? []);

        unset($data['permission_ids']);

        $role->update([
            ...$data,
            'slug' => $data['slug'] ?? Str::slug($data['name']),
        ]);

        $role->permissions()->sync($permissionIds);

        $auditLogService->record(
            module: 'team',
            event: 'role_updated',
            auditable: $role,
            oldValues: $oldValues,
            newValues: $role->fresh()->load('permissions')->toArray(),
            description: __('Role :name updated.', ['name' => __($role->name)]),
        );

        return redirect()->route('roles.show', $role)->with('success', __('Role updated successfully.'));
    }

    public function destroy(Role $role, AuditLogService $auditLogService)
    {
        $this->authorize('delete', $role);

        $auditLogService->record(
            module: 'team',
            event: 'role_deleted',
            auditable: $role,
            oldValues: $role->load('permissions')->toArray(),
            description: __('Role :name deleted.', ['name' => __($role->name)]),
        );

        $role->delete();

        return redirect()->route('roles.index')->with('success', __('Role deleted successfully.'));
    }

    private function withRequiredPermissionIds(array $permissionIds): array
    {
        $permissions = Permission::query()
            ->whereIn('id', $permissionIds)
            ->get(['id', 'slug']);

        $selectedSlugs = $permissions->pluck('slug');
        $requiredViewSlugs = $selectedSlugs
            ->filter(fn (string $slug): bool => str_contains($slug, '.') && ! str_ends_with($slug, '.view'))
            ->map(fn (string $slug): string => str($slug)->before('.')->append('.view')->toString())
            ->unique()
            ->values();

        if ($requiredViewSlugs->isEmpty()) {
            return $permissions->pluck('id')->map(fn ($id) => (int) $id)->all();
        }

        return Permission::query()
            ->whereIn('id', $permissions->pluck('id'))
            ->orWhereIn('slug', $requiredViewSlugs)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }
}

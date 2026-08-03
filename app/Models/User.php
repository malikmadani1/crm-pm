<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

class User extends Authenticatable
{
    public const SUPER_ADMIN_EMAIL = 'admin@crm-pm.test';

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'employee_code',
        'job_title',
        'phone',
        'avatar_path',
        'timezone',
        'locale',
        'is_active',
        'last_seen_at',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = [
        'role_names',
    ];

    protected ?Collection $cachedPermissions = null;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_seen_at' => 'datetime',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    public function directPermissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class)->withTimestamps();
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'owner_id');
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'owner_id');
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class, 'owner_id');
    }

    public function managedProjects(): HasMany
    {
        return $this->hasMany(Project::class, 'manager_id');
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class)
            ->withPivot(['is_primary', 'assigned_at'])
            ->withTimestamps();
    }

    public function createdTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    public function taskComments(): HasMany
    {
        return $this->hasMany(TaskComment::class);
    }

    public function taskLogs(): HasMany
    {
        return $this->hasMany(TaskLog::class);
    }

    public function interactions(): HasMany
    {
        return $this->hasMany(CustomerInteraction::class);
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(FollowUp::class, 'assigned_to');
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function permissions(): Collection
    {
        if ($this->cachedPermissions instanceof Collection) {
            return $this->cachedPermissions;
        }

        $rolePermissions = $this->roles()->with('permissions')->get()
            ->pluck('permissions')
            ->flatten();

        return $this->cachedPermissions = $rolePermissions
            ->merge($this->directPermissions()->get())
            ->unique('id')
            ->values();
    }

    public function hasRole(string|array $roles): bool
    {
        $roles = (array) $roles;

        return $this->roles->contains(
            fn (Role $role) => in_array($role->slug, $roles, true) || in_array($role->name, $roles, true)
        );
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isProtectedSuperAdmin(): bool
    {
        return strcasecmp($this->email, self::SUPER_ADMIN_EMAIL) === 0;
    }

    public function hasPermissionTo(string $permission): bool
    {
        if ($permission === 'dashboard.view') {
            return true;
        }

        if ($this->isAdmin()) {
            return true;
        }

        return $this->permissions()->contains(
            fn (Permission $item) => $item->slug === $permission || $item->name === $permission
        );
    }

    public function syncRolesByIds(array $roleIds): void
    {
        $this->roles()->sync($roleIds);
        $this->cachedPermissions = null;
    }

    public function syncPermissionsByIds(array $permissionIds): void
    {
        $this->directPermissions()->sync($permissionIds);
        $this->cachedPermissions = null;
    }

    public function getRoleNamesAttribute(): array
    {
        return $this->roles->pluck('name')->all();
    }
}

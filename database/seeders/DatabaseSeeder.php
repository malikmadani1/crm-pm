<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Attachment;
use App\Models\Customer;
use App\Models\CustomerInteraction;
use App\Models\Deal;
use App\Models\DealStage;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\Permission;
use App\Models\Project;
use App\Models\Role;
use App\Models\Setting;
use App\Models\Tag;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\TaskLog;
use App\Models\TimeEntry;
use App\Models\User;
use App\Notifications\LeadConvertedNotification;
use App\Notifications\ProjectStatusChangedNotification;
use App\Notifications\TaskAssignedNotification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = $this->seedPermissions();
        [$adminRole, $managerRole, $employeeRole] = $this->seedRoles($permissions);
        $this->seedUsers($adminRole, $managerRole, $employeeRole);
        $this->seedDealStages();
        $this->seedSettings();
    }

    private function seedPermissions(): Collection
    {
        $permissions = collect();

        foreach (config('crm_pm.permissions') as $module => $actions) {
            foreach ($actions as $action) {
                $permissions->push(Permission::query()->create([
                    'name' => Str::title(str_replace('_', ' ', "{$module} {$action}")),
                    'slug' => "{$module}.{$action}",
                    'module' => $module,
                    'description' => "Allows {$action} actions in {$module}.",
                ]));
            }
        }

        return $permissions;
    }

    private function seedRoles(Collection $permissions): array
    {
        $adminRole = Role::query()->create([
            'name' => 'Admin',
            'slug' => 'admin',
            'guard_name' => 'web',
            'description' => 'Full system access.',
            'is_system' => true,
        ]);

        $managerRole = Role::query()->create([
            'name' => 'Manager',
            'slug' => 'manager',
            'guard_name' => 'web',
            'description' => 'Owns teams, customers, projects, and reports.',
            'is_system' => true,
        ]);

        $employeeRole = Role::query()->create([
            'name' => 'Employee',
            'slug' => 'employee',
            'guard_name' => 'web',
            'description' => 'Daily delivery execution access.',
            'is_system' => true,
        ]);

        $adminRole->permissions()->sync($permissions->pluck('id'));

        $managerRole->permissions()->sync(
            $permissions->whereIn('slug', [
                'dashboard.view',
                'users.view', 'users.update',
                'roles.view',
                'customers.view', 'customers.create', 'customers.update', 'customers.delete',
                'leads.view', 'leads.create', 'leads.update', 'leads.delete', 'leads.convert',
                'deals.view', 'deals.create', 'deals.update', 'deals.delete', 'deals.pipeline',
                'projects.view', 'projects.create', 'projects.update', 'projects.delete',
                'tasks.view', 'tasks.create', 'tasks.update', 'tasks.delete', 'tasks.assign', 'tasks.comment', 'tasks.move',
                'time_entries.view', 'time_entries.create', 'time_entries.update', 'time_entries.delete',
                'reports.view', 'reports.export',
                'notifications.view',
                'audit_logs.view',
            ])->pluck('id')
        );

        $employeeRole->permissions()->sync(
            $permissions->whereIn('slug', [
                'dashboard.view',
                'customers.view', 'customers.create', 'customers.update',
                'leads.view', 'leads.create', 'leads.update',
                'deals.view', 'deals.create', 'deals.update', 'deals.pipeline',
                'projects.view',
                'tasks.view', 'tasks.create', 'tasks.update', 'tasks.comment', 'tasks.move',
                'time_entries.view', 'time_entries.create', 'time_entries.update',
                'reports.view',
                'notifications.view',
            ])->pluck('id')
        );

        return [$adminRole, $managerRole, $employeeRole];
    }

    private function seedUsers(Role $adminRole, Role $managerRole, Role $employeeRole): array
    {
        $roleIds = [$adminRole->id, $managerRole->id, $employeeRole->id];
        $accounts = collect([
            [
                'name' => 'Super Admin',
                'email' => 'admin@crm-pm.test',
                'employee_code' => 'ADM-001',
                'job_title' => 'Super Administrator',
            ],
            [
                'name' => 'System Admin 2',
                'email' => 'admin2@crm-pm.test',
                'employee_code' => 'ADM-002',
                'job_title' => 'Operations Administrator',
            ],
            [
                'name' => 'System Admin 3',
                'email' => 'admin3@crm-pm.test',
                'employee_code' => 'ADM-003',
                'job_title' => 'Testing Administrator',
            ],
        ])->map(function (array $account) use ($roleIds) {
            $user = User::factory()->create($account);
            $user->roles()->sync($roleIds);

            return $user;
        });

        return [$accounts->first(), collect(), collect()];
    }

    private function seedDealStages(): Collection
    {
        return collect(config('crm_pm.deal_stages'))
            ->map(fn (array $stage) => DealStage::query()->create($stage));
    }

    private function seedSettings(): void
    {
        collect([
            ['group' => 'branding', 'key' => 'app_name', 'value' => 'CRM & PM System', 'type' => 'string'],
            ['group' => 'branding', 'key' => 'theme_default', 'value' => 'dark', 'type' => 'string'],
            ['group' => 'company', 'key' => 'company_name', 'value' => 'Acme Operations', 'type' => 'string'],
        ])->each(fn ($setting) => Setting::query()->create($setting));
    }

    private function seedCustomers(Collection $owners): Collection
    {
        $customers = collect();

        foreach (range(1, 18) as $index) {
            $customer = Customer::factory()->create([
                'owner_id' => $owners->random()->id,
                'status' => $index <= 10 ? 'active' : fake()->randomElement(['potential', 'not_interested']),
            ]);

            CustomerInteraction::factory(fake()->numberBetween(1, 4))->create([
                'customer_id' => $customer->id,
                'user_id' => $owners->random()->id,
            ]);

            if (fake()->boolean(70)) {
                FollowUp::factory()->create([
                    'customer_id' => $customer->id,
                    'lead_id' => null,
                    'assigned_to' => $owners->random()->id,
                ]);
            }

            $customers->push($customer);
        }

        return $customers;
    }

    private function seedLeads(Collection $owners, Collection $customers): Collection
    {
        $leads = collect();

        foreach (range(1, 14) as $index) {
            $lead = Lead::factory()->create([
                'owner_id' => $owners->random()->id,
            ]);

            if ($index <= 3) {
                $convertedCustomer = Customer::factory()->create([
                    'owner_id' => $lead->owner_id,
                    'name' => $lead->name,
                    'company_name' => $lead->company_name,
                    'email' => $lead->email,
                    'phone' => $lead->phone,
                    'status' => 'active',
                ]);

                $lead->update([
                    'converted_customer_id' => $convertedCustomer->id,
                    'status' => 'converted',
                    'stage' => 'won',
                    'converted_at' => now()->subDays(fake()->numberBetween(1, 12)),
                ]);

                $customers->push($convertedCustomer);
            }

            FollowUp::factory()->create([
                'customer_id' => null,
                'lead_id' => $lead->id,
                'assigned_to' => $owners->random()->id,
            ]);

            $leads->push($lead->fresh());
        }

        return $leads;
    }

    private function seedDeals(Collection $owners, Collection $customers, Collection $leads, Collection $stages): Collection
    {
        $deals = collect();

        foreach (range(1, 24) as $index) {
            $stage = $stages->random();
            $status = $stage->is_won ? 'won' : ($stage->is_lost ? 'lost' : 'open');

            $deals->push(Deal::factory()->create([
                'owner_id' => $owners->random()->id,
                'customer_id' => $customers->random()->id,
                'lead_id' => fake()->boolean(70) ? $leads->random()->id : null,
                'stage_id' => $stage->id,
                'status' => $status,
                'closed_at' => in_array($status, ['won', 'lost'], true) ? now()->subDays(fake()->numberBetween(1, 10)) : null,
            ]));
        }

        return $deals;
    }

    private function seedProjectsAndTasks(Collection $customers, Collection $managers, Collection $employees, Collection $tags): array
    {
        $projects = collect();
        $tasks = collect();

        foreach (range(1, 10) as $index) {
            $manager = $managers->random();
            $project = Project::factory()->create([
                'customer_id' => $customers->random()->id,
                'manager_id' => $manager->id,
                'status' => $index <= 2 ? 'completed' : fake()->randomElement(['in_progress', 'paused', 'on_hold']),
            ]);

            $memberIds = $employees->random(3)->pluck('id')->push($manager->id)->unique();
            $project->members()->sync(
                $memberIds->mapWithKeys(fn ($id) => [$id => ['role' => $id === $manager->id ? 'manager' : 'member']])->all()
            );

            foreach (range(1, fake()->numberBetween(5, 8)) as $taskIndex) {
                $assigneeIds = $employees->random(fake()->numberBetween(1, 2))->pluck('id')->unique();
                $task = Task::factory()->create([
                    'project_id' => $project->id,
                    'created_by' => $manager->id,
                    'status' => $project->status === 'completed' ? 'done' : fake()->randomElement(['todo', 'in_progress', 'review', 'done']),
                ]);

                $task->assignees()->sync(
                    $assigneeIds->values()->mapWithKeys(fn ($id, $i) => [$id => ['is_primary' => $i === 0, 'assigned_at' => now()->subDays(fake()->numberBetween(1, 6))]])->all()
                );
                $task->tags()->sync($tags->random(fake()->numberBetween(1, 3))->pluck('id'));

                TaskComment::factory(fake()->numberBetween(1, 3))->create([
                    'task_id' => $task->id,
                    'user_id' => $assigneeIds->first(),
                ]);

                TaskLog::factory(fake()->numberBetween(2, 4))->create([
                    'task_id' => $task->id,
                    'user_id' => $manager->id,
                ]);

                TimeEntry::factory(fake()->numberBetween(1, 3))->create([
                    'project_id' => $project->id,
                    'task_id' => $task->id,
                    'user_id' => $assigneeIds->first(),
                ]);

                if ($taskIndex <= 2) {
                    Task::factory()->create([
                        'project_id' => $project->id,
                        'parent_id' => $task->id,
                        'created_by' => $manager->id,
                        'title' => $task->title . ' / Subtask',
                        'status' => fake()->randomElement(['todo', 'in_progress', 'done']),
                    ]);
                }

                if (fake()->boolean(35)) {
                    Attachment::factory()->create([
                        'user_id' => $manager->id,
                        'attachable_type' => Task::class,
                        'attachable_id' => $task->id,
                    ]);
                }

                $tasks->push($task->fresh('assignees'));
            }

            $completedTasks = $project->tasks()->where('status', 'done')->count();
            $project->update([
                'progress' => max(5, (int) round(($completedTasks / max($project->tasks()->count(), 1)) * 100)),
            ]);

            $projects->push($project->fresh('members'));
        }

        return [$projects, $tasks];
    }

    private function seedAuditLogs(Collection $users, Collection $customers, Collection $leads, Collection $projects, Collection $tasks): void
    {
        $auditablePool = $customers->concat($leads)->concat($projects)->concat($tasks);

        foreach (range(1, 30) as $index) {
            $model = $auditablePool->random();
            AuditLog::query()->create([
                'user_id' => $users->random()->id,
                'auditable_type' => $model::class,
                'auditable_id' => $model->id,
                'module' => fake()->randomElement(['crm', 'projects', 'tasks', 'team']),
                'event' => fake()->randomElement(['created', 'updated', 'status_changed', 'assigned']),
                'description' => fake()->sentence(),
                'old_values' => ['sample' => 'old'],
                'new_values' => ['sample' => 'new'],
                'url' => fake()->url(),
                'ip_address' => fake()->ipv4(),
                'user_agent' => fake()->userAgent(),
                'created_at' => now()->subDays(fake()->numberBetween(1, 25)),
            ]);
        }
    }

    private function seedNotifications(Collection $projects, Collection $tasks, Collection $leads, Collection $customers, User $admin, Collection $managers): void
    {
        $tasks->take(10)->each(function (Task $task): void {
            $task->assignees->each(fn (User $user) => $user->notify(new TaskAssignedNotification($task)));
        });

        $projects->take(4)->each(function (Project $project): void {
            $project->members()->get()->each(fn (User $user) => $user->notify(
                new ProjectStatusChangedNotification($project, 'planning', $project->status)
            ));
        });

        $leads->where('status', 'converted')->take(3)->values()->each(function (Lead $lead, int $index) use ($customers, $admin): void {
            $customer = $customers->firstWhere('id', $lead->converted_customer_id);

            if ($customer) {
                $admin->notify(new LeadConvertedNotification($lead, $customer));
            }
        });

        $managers->first()?->notify(new ProjectStatusChangedNotification($projects->first(), 'in_progress', $projects->first()->status));
    }
}

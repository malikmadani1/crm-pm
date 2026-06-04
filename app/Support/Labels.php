<?php

namespace App\Support;

use Illuminate\Support\Str;

class Labels
{
    public static function status(string|int|float|null $value): string
    {
        if (is_numeric($value)) {
            return (string) $value;
        }

        $normalized = self::normalize((string) $value);

        return __(match ($normalized) {
            'potential' => 'Potential',
            'active' => 'Active',
            'inactive' => 'Inactive',
            'not_interested' => 'Not Interested',
            'new_lead' => 'New Lead',
            'contacted' => 'Contacted',
            'qualified' => 'Qualified',
            'proposal_sent' => 'Proposal Sent',
            'negotiation' => 'Negotiation',
            'won' => 'Won',
            'lost' => 'Lost',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'paused' => 'Paused',
            'on_hold' => 'On Hold',
            'todo', 'to_do' => 'To Do',
            'review' => 'Review',
            'done' => 'Done',
            'low' => 'Low',
            'medium' => 'Medium',
            'high' => 'High',
            'pending' => 'Pending',
            'cancelled' => 'Cancelled',
            'open' => 'Open',
            'closed' => 'Closed',
            'converted' => 'Converted',
            'yes' => 'Yes',
            'no' => 'No',
            'live_data' => 'Live Data',
            default => self::humanize((string) $value),
        });
    }

    public static function leadStage(string $value): string
    {
        return self::status($value);
    }

    public static function priority(string $value): string
    {
        return self::status($value);
    }

    public static function auditEvent(string $event): string
    {
        return __(match ($event) {
            'customer_created' => 'Customer created',
            'customer_updated' => 'Customer updated',
            'customer_deleted' => 'Customer deleted',
            'lead_created' => 'Lead created',
            'lead_updated' => 'Lead updated',
            'lead_deleted' => 'Lead deleted',
            'lead_converted' => 'Lead converted',
            'deal_created' => 'Deal created',
            'deal_updated' => 'Deal updated',
            'deal_deleted' => 'Deal deleted',
            'deal_stage_updated' => 'Deal stage updated',
            'project_created' => 'Project created',
            'project_updated' => 'Project updated',
            'project_deleted' => 'Project deleted',
            'task_created' => 'Task created',
            'task_updated' => 'Task updated',
            'task_deleted' => 'Task deleted',
            'status_changed' => 'Status changed',
            'comment_added' => 'Comment added',
            'time_entry_created' => 'Time entry created',
            'time_entry_updated' => 'Time entry updated',
            'time_entry_deleted' => 'Time entry deleted',
            'attendance_checked_in' => 'Attendance check-in',
            'attendance_checked_out' => 'Attendance check-out',
            'role_created' => 'Role created',
            'role_updated' => 'Role updated',
            'role_deleted' => 'Role deleted',
            'user_created' => 'User created',
            'user_updated' => 'User updated',
            'user_deleted' => 'User deleted',
            default => self::humanize($event),
        });
    }

    public static function module(string $module): string
    {
        return __(match ($module) {
            'crm' => 'CRM',
            'tasks' => 'Tasks',
            'projects' => 'Projects',
            'team' => 'Team and permissions',
            'reports' => 'Reports',
            default => self::humanize($module),
        });
    }

    public static function field(string $key): string
    {
        return __(match ($key) {
            'name' => 'Name',
            'title' => 'Title',
            'phone' => 'Phone',
            'email' => 'Email',
            'company_name' => 'Company Name',
            'job_title' => 'Job Title',
            'address' => 'Address',
            'city' => 'City',
            'country' => 'Country',
            'source' => 'Source',
            'status' => 'Status',
            'stage' => 'Stage',
            'stage_id' => 'Deal Stage',
            'priority' => 'Priority',
            'description' => 'Description',
            'start_date' => 'Start Date',
            'due_date' => 'Due Date',
            'work_date' => 'Work Date',
            'checked_in_at' => 'Check-in Time',
            'checked_out_at' => 'Check-out Time',
            'worked_minutes' => 'Worked Duration',
            'expected_close_date' => 'Expected Close Date',
            'estimated_hours' => 'Estimated Hours',
            'actual_hours' => 'Actual Hours',
            'completion_percentage' => 'Completion Percentage',
            'value' => 'Value',
            'probability' => 'Probability',
            'budget' => 'Budget',
            'progress' => 'Progress',
            'notes' => 'Notes',
            'owner_id' => 'Owner',
            'manager_id' => 'Project Manager',
            'customer_id' => 'Customer',
            'lead_id' => 'Lead',
            'project_id' => 'Project',
            'parent_id' => 'Parent Task',
            'roles' => 'Roles',
            'permissions' => 'Permissions',
            'members' => 'Members',
            'assignees' => 'Assignees',
            'tags' => 'Tags',
            'is_active' => 'Active',
            'body' => 'Content',
            default => self::humanize($key),
        });
    }

    public static function boolean(bool $value): string
    {
        return $value ? __('Yes') : __('No');
    }

    public static function notAvailable(): string
    {
        return __('Not available');
    }

    public static function none(): string
    {
        return __('None');
    }

    public static function empty(): string
    {
        return __('Empty');
    }

    public static function separator(): string
    {
        return app()->isLocale('ar') ? '، ' : ', ';
    }

    public static function humanize(string $value): string
    {
        return Str::of($value)->replace(['_', '-'], ' ')->title()->toString();
    }

    private static function normalize(string $value): string
    {
        return Str::of($value)->replace('-', '_')->snake()->lower()->toString();
    }
}

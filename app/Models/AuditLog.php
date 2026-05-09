<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class AuditLog extends Model
{
    /** @use HasFactory<\Database\Factories\AuditLogFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'auditable_type',
        'auditable_id',
        'module',
        'event',
        'description',
        'old_values',
        'new_values',
        'url',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    public function eventLabel(): string
    {
        return match ($this->event) {
            'customer_created' => 'إنشاء عميل',
            'customer_updated' => 'تحديث عميل',
            'customer_deleted' => 'حذف عميل',
            'lead_created' => 'إنشاء عميل محتمل',
            'lead_updated' => 'تحديث عميل محتمل',
            'lead_deleted' => 'حذف عميل محتمل',
            'lead_converted' => 'تحويل عميل محتمل إلى عميل',
            'deal_created' => 'إنشاء فرصة بيع',
            'deal_updated' => 'تحديث فرصة بيع',
            'deal_deleted' => 'حذف فرصة بيع',
            'deal_stage_updated' => 'تغيير مرحلة فرصة البيع',
            'project_created' => 'إنشاء مشروع',
            'project_updated' => 'تحديث مشروع',
            'project_deleted' => 'حذف مشروع',
            'task_created' => 'إنشاء مهمة',
            'task_updated' => 'تحديث مهمة',
            'task_deleted' => 'حذف مهمة',
            'status_changed' => 'تغيير حالة المهمة',
            'comment_added' => 'إضافة تعليق',
            'time_entry_created' => 'إضافة وقت عمل',
            'time_entry_updated' => 'تحديث وقت العمل',
            'time_entry_deleted' => 'حذف وقت العمل',
            'attendance_checked_in' => 'تسجيل دخول الدوام',
            'attendance_checked_out' => 'تسجيل خروج الدوام',
            'role_created' => 'إنشاء دور',
            'role_updated' => 'تحديث دور',
            'role_deleted' => 'حذف دور',
            'user_created' => 'إنشاء مستخدم',
            'user_updated' => 'تحديث مستخدم',
            'user_deleted' => 'حذف مستخدم',
            default => Str::of($this->event)->replace('_', ' ')->title()->toString(),
        };
    }

    public function moduleLabel(): string
    {
        return match ($this->module) {
            'crm' => 'إدارة العملاء',
            'tasks' => 'المهام',
            'projects' => 'المشاريع',
            'team' => 'الفريق والصلاحيات',
            'reports' => 'التقارير',
            default => Str::of($this->module)->replace('_', ' ')->title()->toString(),
        };
    }

    public function subjectLabel(): ?string
    {
        $subject = $this->auditable;

        if (! $subject) {
            return null;
        }

        return match (true) {
            isset($subject->title) => (string) $subject->title,
            isset($subject->name) => (string) $subject->name,
            isset($subject->body) => Str::limit((string) $subject->body, 80),
            default => class_basename($subject).' #'.$subject->getKey(),
        };
    }

    public function subjectUrl(): ?string
    {
        $subject = $this->auditable;

        if (! $subject) {
            return null;
        }

        $route = match ($subject::class) {
            Customer::class => ['customers.show', $subject],
            Lead::class => ['leads.show', $subject],
            Deal::class => ['deals.show', $subject],
            Project::class => ['projects.show', $subject],
            Task::class => ['tasks.show', $subject],
            Role::class => ['roles.show', $subject],
            User::class => ['users.show', $subject],
            default => null,
        };

        if (! $route || ! Route::has($route[0])) {
            return null;
        }

        return route($route[0], $route[1]);
    }

    public function summaryLines(int $limit = 3): array
    {
        $lines = $this->changeLines();

        if ($lines === [] && filled($this->description)) {
            $lines = [$this->description];
        }

        return array_slice($lines, 0, $limit);
    }

    public function changeRows(): array
    {
        $oldValues = $this->old_values ?? [];
        $newValues = $this->new_values ?? [];
        $rows = [];

        foreach ($this->relevantKeys() as $key) {
            $oldValue = $this->displayValue($key, Arr::get($oldValues, $key));
            $newValue = $this->displayValue($key, Arr::get($newValues, $key));

            if ($this->isCreateEvent()) {
                if ($newValue !== null && $newValue !== 'غير متوفر') {
                    $rows[] = ['field' => $this->fieldLabel($key), 'old' => null, 'new' => $newValue];
                }

                continue;
            }

            if ($this->isDeleteEvent()) {
                if ($oldValue !== null && $oldValue !== 'غير متوفر') {
                    $rows[] = ['field' => $this->fieldLabel($key), 'old' => $oldValue, 'new' => null];
                }

                continue;
            }

            if ($oldValue === $newValue || ($oldValue === null && $newValue === null)) {
                continue;
            }

            $rows[] = ['field' => $this->fieldLabel($key), 'old' => $oldValue, 'new' => $newValue];
        }

        return $rows;
    }

    public function changeLines(): array
    {
        $lines = [];

        foreach ($this->changeRows() as $row) {
            if ($this->isCreateEvent()) {
                $lines[] = $row['field'].': '.$row['new'];
                continue;
            }

            if ($this->isDeleteEvent()) {
                $lines[] = $row['field'].': '.$row['old'];
                continue;
            }

            $lines[] = $row['field'].': '.($row['old'] ?? 'فارغ').' -> '.($row['new'] ?? 'فارغ');
        }

        return $lines;
    }

    private function relevantKeys(): array
    {
        $keys = match ($this->auditable_type) {
            Customer::class => ['name', 'phone', 'email', 'company_name', 'job_title', 'address', 'city', 'country', 'source', 'status', 'owner_id', 'notes'],
            Lead::class => ['name', 'phone', 'email', 'company_name', 'job_title', 'address', 'city', 'country', 'stage', 'status', 'owner_id', 'notes'],
            Deal::class => ['title', 'value', 'probability', 'expected_close_date', 'status', 'stage_id', 'lead_id', 'customer_id', 'owner_id', 'notes'],
            Project::class => ['name', 'code', 'description', 'start_date', 'due_date', 'budget', 'status', 'priority', 'progress', 'manager_id', 'customer_id', 'members'],
            Task::class => ['title', 'description', 'status', 'priority', 'start_date', 'due_date', 'estimated_hours', 'actual_hours', 'completion_percentage', 'project_id', 'parent_id', 'assignees', 'tags'],
            AttendanceRecord::class => ['work_date', 'checked_in_at', 'checked_out_at', 'worked_minutes', 'notes'],
            Role::class => ['name', 'slug', 'description', 'permissions'],
            User::class => ['name', 'email', 'is_active', 'roles', 'permissions'],
            default => array_values(array_filter(
                array_unique(array_merge(array_keys($this->old_values ?? []), array_keys($this->new_values ?? []))),
                fn (string $key) => ! in_array($key, ['id', 'created_at', 'updated_at', 'deleted_at', 'url', 'ip_address', 'user_agent'], true)
            )),
        };

        return array_values(array_filter($keys, fn (string $key) => ! str_ends_with($key, '_type')));
    }

    private function isCreateEvent(): bool
    {
        return str_ends_with($this->event, '_created');
    }

    private function isDeleteEvent(): bool
    {
        return str_ends_with($this->event, '_deleted');
    }

    private function fieldLabel(string $key): string
    {
        return match ($key) {
            'name' => 'الاسم',
            'title' => 'العنوان',
            'phone' => 'الهاتف',
            'email' => 'البريد الإلكتروني',
            'company_name' => 'اسم الشركة',
            'job_title' => 'المسمى الوظيفي',
            'address' => 'العنوان',
            'city' => 'المدينة',
            'country' => 'الدولة',
            'source' => 'طريقة الوصول',
            'status' => 'الحالة',
            'stage' => 'المرحلة',
            'stage_id' => 'مرحلة البيع',
            'priority' => 'الأولوية',
            'description' => 'الوصف',
            'start_date' => 'تاريخ البدء',
            'due_date' => 'تاريخ الاستحقاق',
            'work_date' => 'تاريخ الدوام',
            'checked_in_at' => 'وقت الدخول',
            'checked_out_at' => 'وقت الخروج',
            'worked_minutes' => 'مدة الدوام',
            'expected_close_date' => 'تاريخ الإغلاق المتوقع',
            'estimated_hours' => 'الساعات المقدرة',
            'actual_hours' => 'الساعات الفعلية',
            'completion_percentage' => 'نسبة الإنجاز',
            'value' => 'القيمة',
            'probability' => 'نسبة الإغلاق',
            'budget' => 'الميزانية',
            'progress' => 'التقدم',
            'notes' => 'الملاحظات',
            'owner_id' => 'المسؤول',
            'manager_id' => 'مدير المشروع',
            'customer_id' => 'العميل',
            'lead_id' => 'العميل المحتمل',
            'project_id' => 'المشروع',
            'parent_id' => 'المهمة الأم',
            'roles' => 'الأدوار',
            'permissions' => 'الصلاحيات',
            'members' => 'الأعضاء',
            'assignees' => 'المكلّفون',
            'tags' => 'الوسوم',
            'is_active' => 'نشط',
            'body' => 'المحتوى',
            default => Str::of($key)->replace('_', ' ')->title()->toString(),
        };
    }

    private function displayValue(string $key, mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return match ($key) {
            'status' => $this->translateStatus((string) $value),
            'stage' => $this->translateLeadStage((string) $value),
            'priority' => $this->translatePriority((string) $value),
            'completion_percentage', 'progress', 'probability' => (int) $value.'%',
            'budget', 'value' => number_format((float) $value, 2),
            'estimated_hours', 'actual_hours' => rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.').' ساعة',
            'worked_minutes' => round(((int) $value) / 60, 2).' ساعة',
            'start_date', 'due_date', 'expected_close_date' => (string) $value,
            'checked_in_at', 'checked_out_at' => (string) $value,
            'is_active' => (bool) $value ? 'نعم' : 'لا',
            'roles', 'permissions', 'members', 'assignees', 'tags' => $this->displayCollectionValues($value),
            default => is_array($value) ? $this->displayCollectionValues($value) : trim((string) $value),
        };
    }

    private function displayCollectionValues(mixed $value): string
    {
        $items = collect(is_array($value) ? $value : [$value])
            ->map(function (mixed $item) {
                if (is_array($item)) {
                    return $item['name']
                        ?? $item['title']
                        ?? $item['slug']
                        ?? $item['email']
                        ?? $item['body']
                        ?? null;
                }

                if (is_scalar($item)) {
                    return (string) $item;
                }

                return null;
            })
            ->filter(fn (?string $item) => filled($item))
            ->values()
            ->all();

        return $items === [] ? 'غير متوفر' : implode('، ', $items);
    }

    private function translateStatus(string $value): string
    {
        return match ($value) {
            'todo' => 'للعمل',
            'in_progress' => 'قيد التنفيذ',
            'review' => 'قيد المراجعة',
            'done' => 'مكتملة',
            'open' => 'مفتوحة',
            'won' => 'رابحة',
            'lost' => 'خاسرة',
            'converted' => 'تم تحويلها',
            'active' => 'نشط',
            'potential' => 'محتمل',
            'not_interested' => 'غير مهتم',
            'completed' => 'مكتمل',
            'paused' => 'متوقف',
            'on_hold' => 'قيد الانتظار',
            default => Str::of($value)->replace('_', ' ')->title()->toString(),
        };
    }

    private function translateLeadStage(string $value): string
    {
        return match ($value) {
            'new_lead' => 'عميل محتمل جديد',
            'contacted' => 'تم التواصل',
            'qualified' => 'مؤهل',
            'proposal_sent' => 'تم إرسال العرض',
            'negotiation' => 'تفاوض',
            'won' => 'رابحة',
            'lost' => 'خاسرة',
            default => Str::of($value)->replace('_', ' ')->title()->toString(),
        };
    }

    private function translatePriority(string $value): string
    {
        return match ($value) {
            'low' => 'منخفضة',
            'medium' => 'متوسطة',
            'high' => 'عالية',
            default => Str::of($value)->replace('_', ' ')->title()->toString(),
        };
    }
}

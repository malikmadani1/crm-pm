<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use App\Support\Duration;
use App\Support\Labels;

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
        return Labels::auditEvent($this->event);
    }

    public function moduleLabel(): string
    {
        return Labels::module($this->module);
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
                if ($newValue !== null && $newValue !== Labels::notAvailable()) {
                    $rows[] = ['field' => $this->fieldLabel($key), 'old' => null, 'new' => $newValue];
                }

                continue;
            }

            if ($this->isDeleteEvent()) {
                if ($oldValue !== null && $oldValue !== Labels::notAvailable()) {
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

            $lines[] = $row['field'].': '.($row['old'] ?? Labels::empty()).' -> '.($row['new'] ?? Labels::empty());
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
        return Labels::field($key);
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
            'estimated_hours', 'actual_hours' => Duration::fromHours((float) $value),
            'worked_minutes' => Duration::fromMinutes((int) $value),
            'start_date', 'due_date', 'expected_close_date' => (string) $value,
            'checked_in_at', 'checked_out_at' => (string) $value,
            'is_active' => Labels::boolean((bool) $value),
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

        return $items === [] ? Labels::notAvailable() : implode(Labels::separator(), $items);
    }

    private function translateStatus(string $value): string
    {
        return Labels::status($value);
    }

    private function translateLeadStage(string $value): string
    {
        return Labels::leadStage($value);
    }

    private function translatePriority(string $value): string
    {
        return Labels::priority($value);
    }
}



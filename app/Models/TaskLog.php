<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Support\Duration;
use App\Support\Labels;

class TaskLog extends Model
{
    /** @use HasFactory<\Database\Factories\TaskLogFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'task_id',
        'user_id',
        'action',
        'description',
        'old_values',
        'new_values',
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

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function titleLabel(): string
    {
        return Labels::auditEvent($this->action);
    }

    public function detailLines(): array
    {
        $lines = $this->summarizeChanges();

        if ($lines !== []) {
            return $lines;
        }

        if (filled($this->description)) {
            return [(string) __($this->description)];
        }

        return [__('No additional details.')];
    }

    private function summarizeChanges(): array
    {
        $oldValues = $this->old_values ?? [];
        $newValues = $this->new_values ?? [];
        $keys = match ($this->action) {
            'task_created' => ['status', 'priority', 'due_date', 'completion_percentage', 'assignees', 'tags'],
            'task_updated' => ['title', 'description', 'status', 'priority', 'start_date', 'due_date', 'estimated_hours', 'actual_hours', 'completion_percentage', 'assignees', 'tags'],
            'status_changed' => ['status', 'completion_percentage'],
            default => [],
        };

        $lines = [];

        foreach ($keys as $key) {
            $oldValue = $this->displayValue($key, Arr::get($oldValues, $key));
            $newValue = $this->displayValue($key, Arr::get($newValues, $key));

            if ($this->action === 'task_created') {
                if ($newValue !== null && $newValue !== Labels::none()) {
                    $lines[] = $this->fieldLabel($key).': '.$newValue;
                }

                continue;
            }

            if ($oldValue === $newValue || ($oldValue === null && $newValue === null)) {
                continue;
            }

            $lines[] = $this->fieldLabel($key).': '.($oldValue ?? Labels::empty()).' -> '.($newValue ?? Labels::empty());
        }

        return $lines;
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
            'status' => Labels::status((string) $value),
            'priority' => Labels::priority((string) $value),
            'completion_percentage' => (int) $value.'%',
            'estimated_hours', 'actual_hours' => Duration::fromHours((float) $value),
            'assignees', 'tags' => $this->displayRelatedNames($value),
            default => is_string($value) ? trim($value) : (string) $value,
        };
    }

    private function displayRelatedNames(mixed $value): string
    {
        $names = collect(is_array($value) ? $value : [])
            ->map(function (mixed $item) {
                if (is_array($item)) {
                    return $item['name'] ?? $item['title'] ?? null;
                }

                return null;
            })
            ->filter()
            ->values()
            ->all();

        return $names === [] ? Labels::none() : implode(Labels::separator(), $names);
    }
}



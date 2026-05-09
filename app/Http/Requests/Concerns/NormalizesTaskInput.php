<?php

namespace App\Http\Requests\Concerns;

trait NormalizesTaskInput
{
    protected function normalizeTaskInput(): void
    {
        $this->merge([
            'parent_id' => $this->blankToNull($this->input('parent_id')),
            'description' => $this->blankToNull($this->input('description')),
            'start_date' => $this->blankToNull($this->input('start_date')),
            'due_date' => $this->blankToNull($this->input('due_date')),
            'estimated_hours' => $this->blankToNull($this->input('estimated_hours')),
            'actual_hours' => $this->blankToDefault($this->input('actual_hours'), 0),
            'completion_percentage' => $this->blankToDefault($this->input('completion_percentage'), 0),
            'assignee_ids' => array_values(array_filter((array) $this->input('assignee_ids', []), fn ($value) => $value !== null && $value !== '')),
            'tag_ids' => array_values(array_filter((array) $this->input('tag_ids', []), fn ($value) => $value !== null && $value !== '')),
        ]);
    }

    protected function blankToNull(mixed $value): mixed
    {
        return $value === '' ? null : $value;
    }

    protected function blankToDefault(mixed $value, mixed $default): mixed
    {
        return $value === '' || $value === null ? $default : $value;
    }
}

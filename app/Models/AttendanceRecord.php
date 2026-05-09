<?php

namespace App\Models;

use App\Support\Duration;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceRecord extends Model
{
    /** @use HasFactory<\Database\Factories\AttendanceRecordFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_date',
        'checked_in_at',
        'checked_out_at',
        'worked_minutes',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'work_date' => 'date',
            'checked_in_at' => 'datetime',
            'checked_out_at' => 'datetime',
            'worked_minutes' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isOpen(): bool
    {
        return $this->checked_in_at !== null && $this->checked_out_at === null;
    }

    public function workedHours(): float
    {
        return round(($this->worked_minutes ?? 0) / 60, 2);
    }

    public function workedSeconds(): int
    {
        if ($this->checked_in_at === null) {
            return (int) round((($this->worked_minutes ?? 0) * 60));
        }

        $endedAt = $this->checked_out_at ?? now();

        return max(0, $this->checked_in_at->diffInSeconds($endedAt));
    }

    public function workedDurationLabel(): string
    {
        return Duration::fromSeconds($this->workedSeconds());
    }
}

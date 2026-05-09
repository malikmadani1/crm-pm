<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Deal extends Model
{
    /** @use HasFactory<\Database\Factories\DealFactory> */
    use HasFactory, SoftDeletes;

    public const STATUSES = ['open', 'won', 'lost'];

    protected $fillable = [
        'lead_id',
        'customer_id',
        'owner_id',
        'stage_id',
        'title',
        'value',
        'probability',
        'expected_close_date',
        'status',
        'closed_at',
        'notes',
    ];

    protected $attributes = [
        'value' => 0,
        'probability' => 20,
        'status' => 'open',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'expected_close_date' => 'date',
            'closed_at' => 'datetime',
        ];
    }

    protected function setValueAttribute($value): void
    {
        $this->attributes['value'] = $value ?? 0;
    }

    protected function setProbabilityAttribute($value): void
    {
        $this->attributes['probability'] = $value ?? 20;
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(DealStage::class, 'stage_id');
    }
}

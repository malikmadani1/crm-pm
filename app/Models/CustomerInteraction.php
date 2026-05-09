<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerInteraction extends Model
{
    /** @use HasFactory<\Database\Factories\CustomerInteractionFactory> */
    use HasFactory;

    public const TYPES = ['call', 'email', 'meeting', 'note', 'follow_up'];

    protected $fillable = [
        'customer_id',
        'user_id',
        'type',
        'subject',
        'details',
        'interaction_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'interaction_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

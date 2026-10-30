<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpendingClosingItem extends Model
{
    protected $fillable = [
        'closing_id',
        'user_id',
        'position_id',
        'target_amount',
        'total_amount',
        'is_met',
    ];

    protected function casts(): array
    {
        return [
            'target_amount' => 'integer',
            'total_amount' => 'integer',
            'is_met' => 'boolean',
        ];
    }

    public function closing(): BelongsTo
    {
        return $this->belongsTo(SpendingClosing::class, 'closing_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }
}


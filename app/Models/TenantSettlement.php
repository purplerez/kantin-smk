<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantSettlement extends Model
{
    protected $fillable = [
        'tenant_id', 'date', 'orders_count', 'gross', 'commission', 'net', 'generated_by',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'orders_count' => 'integer',
            'gross' => 'integer',
            'commission' => 'integer',
            'net' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantHours extends Model
{
    protected $fillable = ['tenant_id', 'day_of_week', 'is_open', 'open_at', 'close_at'];

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'is_open'     => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /** Nama hari dalam Bahasa Indonesia. */
    public function dayName(): string
    {
        return ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'][$this->day_of_week] ?? '?';
    }
}


<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_SUSPENDED = 'suspended';

    protected $fillable = [
        'name', 'slug', 'description', 'image_url', 'status', 'is_open', 'is_koperasi',
        'bank_name', 'bank_account', 'bank_holder',
    ];

    protected function casts(): array
    {
        return [
            'is_open'    => 'boolean',
            'is_koperasi' => 'boolean',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function staff(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function spendingTargets(): HasMany
    {
        return $this->hasMany(SpendingTarget::class);
    }

    public function spendingClosings(): HasMany
    {
        return $this->hasMany(SpendingClosing::class);
    }

    public function hours(): HasMany
    {
        return $this->hasMany(TenantHours::class)->orderBy('day_of_week');
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function canSell(): bool
    {
        return $this->isActive() && $this->is_open;
    }

    public function averageRating(): ?float
    {
        $avg = $this->reviews()->avg('rating');

        return $avg ? round((float) $avg, 1) : null;
    }

    // ----------------------------------------------------------------
    // Jam Operasional
    // ----------------------------------------------------------------

    /**
     * Apakah toko sedang dalam jam operasional?
     *
     * - Jika belum ada jadwal tersimpan → gunakan flag is_open (perilaku lama, backward-compat).
     * - Jika jadwal ada → cek hari & jam sekarang.
     */
    public function isWithinOperatingHours(?Carbon $at = null): bool
    {
        $at ??= now()->setTimezone('Asia/Jakarta');

        // Pastikan relasi sudah di-load agar tidak N+1 di loop
        $hoursCollection = $this->relationLoaded('hours') ? $this->hours : $this->hours()->get();

        if ($hoursCollection->isEmpty()) {
            // Belum ada jadwal → pakai toggle is_open
            return $this->is_open;
        }

        $dayHours = $hoursCollection->firstWhere('day_of_week', $at->dayOfWeek);

        if (! $dayHours) {
            // Hari ini tidak ada entry → anggap tutup
            return false;
        }

        if (! $dayHours->is_open) {
            return false; // hari libur
        }

        $timeNow = $at->format('H:i:s');

        return $timeNow >= $dayHours->open_at && $timeNow < $dayHours->close_at;
    }

    /**
     * Waktu buka berikutnya — untuk ditampilkan ke pembeli di banner PO.
     * Mencari dalam 7 hari ke depan; kembalikan null jika tidak ditemukan.
     */
    public function nextOpenAt(?Carbon $from = null): ?Carbon
    {
        $tz  = 'Asia/Jakarta';
        $now = ($from ?? now())->setTimezone($tz);

        $hoursCollection = $this->relationLoaded('hours') ? $this->hours : $this->hours()->get();

        if ($hoursCollection->isEmpty()) {
            return null; // tidak ada jadwal → tidak bisa prediksi
        }

        for ($i = 0; $i <= 7; $i++) {
            $day     = $now->copy()->addDays($i);
            $dayRow  = $hoursCollection->firstWhere('day_of_week', $day->dayOfWeek);

            if (! $dayRow || ! $dayRow->is_open) {
                continue;
            }

            $openAt = Carbon::parse($day->format('Y-m-d') . ' ' . $dayRow->open_at, $tz);

            if ($openAt->gt($now)) {
                return $openAt;
            }
        }

        return null;
    }
}

<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Preparing = 'preparing';
    case Ready = 'ready';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu Pembayaran',
            self::Confirmed => 'Dikonfirmasi',
            self::Preparing => 'Sedang Disiapkan',
            self::Ready => 'Siap Diambil',
            self::Completed => 'Selesai',
            self::Cancelled => 'Dibatalkan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'bg-amber-100 text-amber-800',
            self::Confirmed => 'bg-sky-100 text-sky-800',
            self::Preparing => 'bg-orange-100 text-orange-800',
            self::Ready => 'bg-brand/10 text-brand-dark',
            self::Completed => 'bg-slate-200 text-slate-700',
            self::Cancelled => 'bg-rose-100 text-rose-800',
        };
    }

    /** Status berikutnya yang boleh dipilih tenant. */
    public function next(): ?self
    {
        return match ($this) {
            self::Confirmed => self::Preparing,
            self::Preparing => self::Ready,
            self::Ready => self::Completed,
            default => null,
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::Completed, self::Cancelled], true);
    }

    /** Urutan langkah untuk timeline pembeli. */
    public static function timeline(): array
    {
        return [self::Confirmed, self::Preparing, self::Ready, self::Completed];
    }
}

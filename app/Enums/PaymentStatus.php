<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Refunded = 'refunded';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Belum Dibayar',
            self::Paid => 'Lunas',
            self::Refunded => 'Dikembalikan',
            self::Cancelled => 'Dibatalkan',
        };
    }
}

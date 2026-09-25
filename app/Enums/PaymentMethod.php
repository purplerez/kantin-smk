<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Qris = 'qris';
    case Transfer = 'transfer';
    case Cash = 'cash';

    public function label(): string
    {
        return match ($this) {
            self::Qris => 'QRIS',
            self::Transfer => 'Transfer Bank',
            self::Cash => 'Tunai di Kasir',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Qris => 'Scan kode QR (simulasi demo)',
            self::Transfer => 'Virtual Account BCA / Mandiri (simulasi demo)',
            self::Cash => 'Bayar langsung saat mengambil pesanan',
        };
    }

    /** Pembayaran non-tunai harus dibayar dulu sebelum tenant memproses. */
    public function requiresPrepayment(): bool
    {
        return $this !== self::Cash;
    }
}

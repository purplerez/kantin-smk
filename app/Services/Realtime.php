<?php

namespace App\Services;

use App\Events\OrderUpdated;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Pembungkus broadcast: dijalankan setelah commit dan tidak pernah menggagalkan transaksi
 * bila server Reverb mati (UI otomatis jatuh ke polling).
 */
class Realtime
{
    public static function enabled(): bool
    {
        return config('broadcasting.default') === 'reverb' && filled(config('broadcasting.connections.reverb.key'));
    }

    public static function orderUpdated(Order $order, string $kind): void
    {
        if (! self::enabled()) {
            return;
        }

        DB::afterCommit(function () use ($order, $kind) {
            try {
                broadcast(new OrderUpdated($order->fresh(), $kind));
            } catch (Throwable $e) {
                report($e);
            }
        });
    }
}

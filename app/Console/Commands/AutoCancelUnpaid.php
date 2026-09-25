<?php

namespace App\Console\Commands;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Services\OrderStatusService;
use Illuminate\Console\Command;

class AutoCancelUnpaid extends Command
{
    protected $signature = 'kantin:auto-cancel-unpaid';

    protected $description = 'Batalkan pesanan tunai yang confirmed tapi belum dibayar melewati batas waktu';

    public function handle(OrderStatusService $service): int
    {
        $minutes = (int) env('CASH_AUTOCANCEL_MINUTES', 60);
        $cutoff = now()->subMinutes($minutes);

        $orders = Order::withoutGlobalScopes()
            ->whereHas('invoice', fn ($q) => $q->where('payment_method', PaymentMethod::Cash->value))
            ->where('status', OrderStatus::Confirmed)
            ->where('payment_status', PaymentStatus::Pending)
            ->where('confirmed_at', '<', $cutoff)
            ->get();

        foreach ($orders as $order) {
            $service->cancel($order, "Dibatalkan otomatis: belum dibayar dalam {$minutes} menit");
        }

        $this->info("Auto-cancel: {$orders->count()} pesanan tunai kedaluwarsa dibatalkan (batas {$minutes} menit).");

        return self::SUCCESS;
    }
}

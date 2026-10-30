<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Scopes\TenantScope;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Layanan Auto-Confirm Pre-Order.
 *
 * Dipanggil oleh scheduler tiap menit (routes/console.php) dan oleh CronController
 * sebagai fallback untuk shared-hosting.
 *
 * Logika:
 *   1. Ambil semua order berstatus pre_order beserta relasi tenant.hours.
 *   2. Periksa apakah tenant sedang dalam jam operasional (menggunakan Carbon::now()
 *      di timezone Asia/Jakarta).
 *   3. Jika ya → ubah status ke confirmed dan kirim notifikasi ke pembeli & tenant.
 */
class PreOrderService
{
    public function __construct(
        private NotificationService $notifications,
    ) {}

    /**
     * Auto-confirm semua PO yang jam bukanya sudah tiba.
     *
     * @return int Jumlah PO yang dikonfirmasi.
     */
    public function autoConfirmDue(?Carbon $at = null): int
    {
        $at ??= now()->setTimezone('Asia/Jakarta');

        $orders = Order::withoutGlobalScope(TenantScope::class)
            ->where('status', OrderStatus::PreOrder)
            ->with(['buyer', 'tenant.hours'])
            ->get();

        $confirmed = 0;

        foreach ($orders as $order) {
            if (! $order->tenant->isWithinOperatingHours($at)) {
                continue;
            }

            DB::transaction(function () use ($order) {
                $order->update([
                    'status'       => OrderStatus::Confirmed,
                    'confirmed_at' => now(),
                ]);

                AuditLogger::log('order.pre_order_confirmed', $order, [
                    'tenant' => $order->tenant->name,
                ]);

                // Notifikasi pembeli: status berubah ke dikonfirmasi
                $this->notifications->notifyUser((int) $order->buyer_id, 'order.status', [
                    'order'  => $order->code,
                    'status' => OrderStatus::Confirmed->label(),
                ]);

                // Notifikasi tenant: ada pesanan baru yang siap diproses
                $this->notifications->notifyTenant((int) $order->tenant_id, 'order.new', [
                    'order'  => $order->code,
                    'buyer'  => $order->buyer->name ?? '—',
                    'amount' => $order->subtotal,
                ]);

                Realtime::orderUpdated($order, 'pre_order_confirmed');
            });

            $confirmed++;
        }

        return $confirmed;
    }
}


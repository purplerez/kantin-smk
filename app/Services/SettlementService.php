<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Tenant;
use App\Models\TenantSettlement;
use App\Models\User;
use Illuminate\Support\Carbon;

class SettlementService
{
    /** Persen komisi platform (dari .env COMMISSION_PERCENT). */
    public function commissionPercent(): float
    {
        return (float) env('COMMISSION_PERCENT', 0);
    }

    /**
     * Hitung & simpan settlement harian untuk semua tenant aktif pada tanggal tertentu.
     * Idempoten (updateOrCreate per tenant+date).
     *
     * @return int jumlah baris settlement yang ditulis
     */
    public function generateForDate(string|Carbon $date, ?User $by = null): int
    {
        $day = Carbon::parse($date)->toDateString();
        $percent = $this->commissionPercent();
        $count = 0;

        foreach (Tenant::pluck('id') as $tenantId) {
            $rows = Order::withoutGlobalScopes()
                ->where('tenant_id', $tenantId)
                ->where('payment_status', PaymentStatus::Paid)
                ->where('status', '!=', OrderStatus::Cancelled)
                ->whereDate('created_at', $day)
                ->get(['subtotal']);

            $gross = (int) $rows->sum('subtotal');
            $ordersCount = $rows->count();
            $commission = (int) round($gross * $percent / 100);
            $net = $gross - $commission;

            TenantSettlement::updateOrCreate(
                ['tenant_id' => $tenantId, 'date' => $day],
                [
                    'orders_count' => $ordersCount,
                    'gross' => $gross,
                    'commission' => $commission,
                    'net' => $net,
                    'generated_by' => $by?->id,
                ],
            );
            $count++;
        }

        return $count;
    }
}

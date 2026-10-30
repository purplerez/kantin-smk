<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Scopes\TenantScope;
use App\Models\SpendingAdjustment;
use App\Models\SpendingClosing;
use App\Models\SpendingClosingItem;
use App\Models\SpendingTarget;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;

class SpendingTotalCalculator
{
    public const TIMEZONE = 'Asia/Jakarta';

    /**
     * Hitung total belanja pegawai / pengguna pada tenant koperasi untuk bulan tertentu.
     *
     * @param  User  $user
     * @param  Tenant  $koperasi
     * @param  CarbonInterface|string|null  $month  Format YYYY-MM atau objek Carbon
     * @return array
     */
    public function calculateForUser(User $user, Tenant $koperasi, CarbonInterface|string|null $month = null): array
    {
        [$period, $startUtc, $endUtc, $startWib] = $this->resolveDateBoundaries($month);

        // 1. Cek apakah bulan ini sudah ditutup (closed snapshot)
        $closing = SpendingClosing::with('items')
            ->where('tenant_id', $koperasi->id)
            ->where('period', $period)
            ->first();

        if ($closing) {
            $closingItem = SpendingClosingItem::with('position')
                ->where('closing_id', $closing->id)
                ->where('user_id', $user->id)
                ->first();

            if ($closingItem) {
                $hasTarget = $closingItem->position_id !== null && $closingItem->target_amount > 0;

                return [
                    'period' => $period,
                    'user_id' => $user->id,
                    'tenant_id' => $koperasi->id,
                    'has_target' => $hasTarget,
                    'position' => $closingItem->position,
                    'position_name' => $closingItem->position?->name ?? 'Siswa / Non-Pegawai',
                    'total_verified' => (int) $closingItem->total_amount,
                    'orders_subtotal' => (int) $closingItem->total_amount, // snapshot total agregat
                    'adjustments_total' => 0,
                    'pending_verification' => 0,
                    'target' => (int) $closingItem->target_amount,
                    'remaining' => $hasTarget ? max(0, (int) $closingItem->target_amount - (int) $closingItem->total_amount) : 0,
                    'is_met' => (bool) $closingItem->is_met,
                    'is_closed' => true,
                    'closed_at' => $closing->closed_at,
                    'orders' => new Collection(),
                    'pending_orders' => new Collection(),
                    'adjustments' => new Collection(),
                ];
            }
        }

        // 2. Query order lunas koperasi dalam rentang bulan [awal bulan, awal bulan berikutnya)
        $verifiedOrders = Order::withoutGlobalScope(TenantScope::class)
            ->with(['items', 'enteredBy'])
            ->where('tenant_id', $koperasi->id)
            ->where('buyer_id', $user->id)
            ->where('payment_status', PaymentStatus::Paid)
            ->where('status', '!=', OrderStatus::Cancelled)
            ->where('paid_at', '>=', $startUtc)
            ->where('paid_at', '<', $endUtc)
            ->orderByDesc('paid_at')
            ->get();

        $ordersSubtotal = (int) $verifiedOrders->sum('subtotal');

        // 3. Query penyesuaian (spending_adjustments) untuk bulan ini
        $adjustments = SpendingAdjustment::where('tenant_id', $koperasi->id)
            ->where('user_id', $user->id)
            ->where('period', $period)
            ->orderBy('created_at')
            ->get();

        $adjustmentsTotal = (int) $adjustments->sum('amount');
        $totalVerified = $ordersSubtotal + $adjustmentsTotal;

        // 4. Query order belum diverifikasi ("menunggu verifikasi", tidak batal)
        $pendingOrders = Order::withoutGlobalScope(TenantScope::class)
            ->with(['items', 'enteredBy'])
            ->where('tenant_id', $koperasi->id)
            ->where('buyer_id', $user->id)
            ->where('payment_status', '!=', PaymentStatus::Paid)
            ->where('status', '!=', OrderStatus::Cancelled)
            ->orderByDesc('created_at')
            ->get();

        $pendingVerification = (int) $pendingOrders->sum('subtotal');

        // 5. Tentukan target yang berlaku
        $user->loadMissing('position');
        $position = $user->position;
        $hasTarget = $position !== null;
        $targetAmount = 0;

        if ($hasTarget) {
            $effectiveDate = $startWib->toDateString(); // YYYY-MM-01

            $target = SpendingTarget::where('tenant_id', $koperasi->id)
                ->where('position_id', $position->id)
                ->whereDate('effective_from', '<=', $effectiveDate)
                ->where(function ($q) use ($effectiveDate) {
                    $q->whereNull('effective_to')
                        ->orWhereDate('effective_to', '>=', $effectiveDate);
                })
                ->orderByDesc('effective_from')
                ->orderByDesc('id')
                ->first();

            $targetAmount = $target ? (int) $target->amount : 0;
        }

        $remaining = $hasTarget ? max(0, $targetAmount - $totalVerified) : 0;
        $isMet = $hasTarget ? ($totalVerified >= $targetAmount) : true;

        return [
            'period' => $period,
            'user_id' => $user->id,
            'tenant_id' => $koperasi->id,
            'has_target' => $hasTarget,
            'position' => $position,
            'position_name' => $position?->name ?? 'Siswa / Non-Pegawai',
            'total_verified' => $totalVerified,
            'orders_subtotal' => $ordersSubtotal,
            'adjustments_total' => $adjustmentsTotal,
            'pending_verification' => $pendingVerification,
            'target' => $targetAmount,
            'remaining' => $remaining,
            'is_met' => $isMet,
            'is_closed' => false,
            'closed_at' => null,
            'orders' => $verifiedOrders,
            'pending_orders' => $pendingOrders,
            'adjustments' => $adjustments,
        ];
    }

    /**
     * Cari tenant koperasi aktif dalam sistem.
     */
    public function getKoperasiTenant(): ?Tenant
    {
        return Tenant::where('is_koperasi', true)->first()
            ?? Tenant::where('slug', 'koperasi-siswa')->first();
    }

    /**
     * Konversi parameter bulan ke boundary waktu Asia/Jakarta dan UTC untuk query DB.
     *
     * @return array{0: string, 1: Carbon, 2: Carbon, 3: Carbon} [$period, $startUtc, $endUtc, $startWib]
     */
    public function resolveDateBoundaries(CarbonInterface|string|null $month = null): array
    {
        if ($month instanceof CarbonInterface) {
            $date = Carbon::instance($month)->setTimezone(self::TIMEZONE);
        } elseif (is_string($month) && preg_match('/^\d{4}-\d{2}$/', $month)) {
            $date = Carbon::createFromFormat('!Y-m', $month, self::TIMEZONE)->startOfMonth();
        } elseif (is_string($month) && ! empty($month)) {
            $date = Carbon::parse($month, self::TIMEZONE);
        } else {
            $date = Carbon::now(self::TIMEZONE);
        }

        $startWib = $date->copy()->startOfMonth();
        $endWib = $startWib->copy()->addMonth();

        $appTimezone = config('app.timezone', 'UTC');
        $startUtc = $startWib->copy()->setTimezone($appTimezone);
        $endUtc = $endWib->copy()->setTimezone($appTimezone);

        $period = $startWib->format('Y-m');

        return [$period, $startUtc, $endUtc, $startWib];
    }
}

<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function view(User $user, Order $order): bool
    {
        return $user->isAdmin()
            || ($user->isBuyer() && $order->buyer_id === $user->id)
            || ($user->isTenantRole() && $order->tenant_id === $user->tenant_id);
    }

    /** Ubah status operasional (confirmed -> preparing -> ready -> completed). */
    public function advance(User $user, Order $order): bool
    {
        if ($order->status->isFinal() || $order->status->next() === null) {
            return false;
        }

        return $user->isAdmin() || ($user->isTenantRole() && $order->tenant_id === $user->tenant_id);
    }

    public function cancel(User $user, Order $order): bool
    {
        if ($order->status->isFinal()) {
            return false;
        }

        if ($user->isBuyer()) {
            return $order->buyer_id === $user->id && $order->canBeCancelledByBuyer();
        }

        return $user->isAdmin() || ($user->isTenantAdmin() && $order->tenant_id === $user->tenant_id);
    }

    /** Verifikasi pembayaran tunai / refund manual. */
    public function verifyPayment(User $user, Order $order): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($order->tenant_id !== $user->tenant_id) {
            return false;
        }

        // Staf koperasi diizinkan memverifikasi pembayaran
        if ($user->isTenantRole() && ($user->tenant?->is_koperasi ?? false)) {
            return true;
        }

        return $user->isTenantAdmin();
    }

    /** Batalkan / void entri kasir langsung. */
    public function voidCounter(User $user, Order $order): bool
    {
        if ($order->source !== 'counter') {
            return false;
        }

        if ($order->status->isFinal() && $order->status->value === 'cancelled') {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if ($order->tenant_id !== $user->tenant_id) {
            return false;
        }

        if ($user->isTenantRole() && ($user->tenant?->is_koperasi ?? false)) {
            return true;
        }

        return $user->isTenantAdmin();
    }

    public function review(User $user, Order $order): bool
    {
        return $user->isBuyer() && $order->buyer_id === $user->id && $order->canBeReviewed();
    }
}

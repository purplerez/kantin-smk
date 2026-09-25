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
        return $user->isAdmin() || ($user->isTenantAdmin() && $order->tenant_id === $user->tenant_id);
    }

    public function review(User $user, Order $order): bool
    {
        return $user->isBuyer() && $order->buyer_id === $user->id && $order->canBeReviewed();
    }
}

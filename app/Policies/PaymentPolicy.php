<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    /**
     * Konfirmasi pembayaran prepay (transfer/qris) secara manual.
     * Platform admin selalu boleh. Tenant admin hanya bila SELURUH order di invoice
     * itu milik tenant-nya (invoice single-tenant). tenant_staf tidak pernah boleh.
     */
    public function confirm(User $user, Payment $payment): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if (! $user->isTenantAdmin()) {
            return false;
        }

        $tenantIds = $payment->invoice->orders()->pluck('tenant_id')->unique();

        return $tenantIds->count() === 1 && (int) $tenantIds->first() === (int) $user->tenant_id;
    }

    public function refund(User $user, Payment $payment): bool
    {
        return $this->confirm($user, $payment);
    }
}

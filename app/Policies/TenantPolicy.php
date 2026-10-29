<?php

namespace App\Policies;

use App\Models\Tenant;
use App\Models\User;

class TenantPolicy
{
    /** Ubah pengaturan tenant (buka/tutup, bank, menu). Peran DAN tenant_id harus cocok. */
    public function manage(User $user, Tenant $tenant): bool
    {
        return $user->isAdmin() || ($user->isTenantAdmin() && (int) $user->tenant_id === (int) $tenant->id);
    }

    /** Persetujuan / penangguhan tenant hanya platform admin. */
    public function moderate(User $user, Tenant $tenant): bool
    {
        return $user->isAdmin();
    }

    public function settlement(User $user, Tenant $tenant): bool
    {
        return $this->manage($user, $tenant);
    }

    /** Akses rekapitulasi belanja pegawai bulanan koperasi. */
    public function viewSpendingRecap(User $user, Tenant $tenant): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $tenant->is_koperasi && $user->isTenantAdmin() && (int) $user->tenant_id === (int) $tenant->id;
    }

    /** Kelola jabatan, target belanja, tutup buku, dan penyesuaian koperasi. */
    public function manageSpending(User $user, Tenant $tenant): bool
    {
        return $this->viewSpendingRecap($user, $tenant);
    }
}

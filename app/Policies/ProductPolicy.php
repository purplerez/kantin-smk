<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function create(User $user): bool
    {
        return $user->isTenantAdmin() || $user->isAdmin();
    }

    public function update(User $user, Product $product): bool
    {
        return $user->isAdmin() || ($user->isTenantAdmin() && $product->tenant_id === $user->tenant_id);
    }

    public function delete(User $user, Product $product): bool
    {
        return $this->update($user, $product);
    }

    /** Tenant staf boleh toggle "habis hari ini" tanpa mengedit produk. */
    public function toggleAvailability(User $user, Product $product): bool
    {
        return $user->isAdmin() || ($user->isTenantRole() && $product->tenant_id === $user->tenant_id);
    }
}

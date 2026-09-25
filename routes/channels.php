<?php

use Illuminate\Support\Facades\Broadcast;

// Kanal privat per tenant: tenant admin/staf tenant tsb + platform admin.
Broadcast::channel('tenant.{tenantId}', function ($user, int $tenantId) {
    return $user->isAdmin() || ($user->isTenantRole() && (int) $user->tenant_id === $tenantId);
});

// Kanal privat per pembeli.
Broadcast::channel('buyer.{userId}', function ($user, int $userId) {
    return (int) $user->id === $userId;
});

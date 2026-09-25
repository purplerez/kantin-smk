<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    public function notifyUser(int $userId, string $type, array $payload = []): void
    {
        Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'payload' => $payload,
            'created_at' => now(),
        ]);
    }

    /** Kirim ke semua staf/admin aktif milik tenant tersebut. */
    public function notifyTenant(int $tenantId, string $type, array $payload = []): void
    {
        User::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->whereIn('role', ['tenant_admin', 'tenant_staf'])
            ->pluck('id')
            ->each(fn ($id) => $this->notifyUser((int) $id, $type, $payload));
    }
}

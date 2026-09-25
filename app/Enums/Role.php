<?php

namespace App\Enums;

enum Role: string
{
    case Admin = 'admin';
    case TenantAdmin = 'tenant_admin';
    case TenantStaf = 'tenant_staf';
    case User = 'user';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Platform Admin',
            self::TenantAdmin => 'Tenant Admin',
            self::TenantStaf => 'Tenant Staf',
            self::User => 'Pembeli',
        };
    }

    public function isTenant(): bool
    {
        return in_array($this, [self::TenantAdmin, self::TenantStaf], true);
    }

    public function homeRoute(): string
    {
        return match ($this) {
            self::Admin => 'admin.dashboard',
            self::TenantAdmin => 'tenant.dashboard',
            self::TenantStaf => 'tenant.orders',
            self::User => 'catalog',
        };
    }
}

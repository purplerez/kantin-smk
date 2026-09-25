<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Global scope isolasi data tenant.
 * Tenant Admin / Tenant Staf hanya bisa melihat baris milik tenant-nya sendiri.
 * Admin platform dan pembeli tidak dibatasi oleh scope ini.
 */
class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = auth()->user();

        if ($user && $user->role->isTenant()) {
            $builder->where($model->qualifyColumn('tenant_id'), $user->tenant_id);
        }
    }
}

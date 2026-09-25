<?php

namespace App\Models;

use App\Enums\Role;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name', 'email', 'identifier', 'password', 'role', 'tenant_id', 'is_active', 'must_change_password',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'role' => Role::class,
            'is_active' => 'boolean',
            'must_change_password' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'buyer_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'buyer_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === Role::Admin;
    }

    public function isTenantAdmin(): bool
    {
        return $this->role === Role::TenantAdmin;
    }

    public function isTenantRole(): bool
    {
        return $this->role->isTenant();
    }

    public function isBuyer(): bool
    {
        return $this->role === Role::User;
    }

    public function initials(): string
    {
        return collect(explode(' ', $this->name))->take(2)->map(fn ($w) => mb_substr($w, 0, 1))->implode('');
    }
}

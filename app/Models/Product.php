<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id', 'category_id', 'name', 'description', 'price', 'image_url', 'is_available_today',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'is_available_today' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /** Item terjual dari pesanan yang sudah selesai (untuk laporan menu terlaris). */
    public function orderItemsCompleted(): HasMany
    {
        return $this->orderItems()->whereHas('order', fn ($q) => $q->withoutGlobalScopes()->where('status', 'completed'));
    }

    /** Produk yang boleh dibeli: tersedia hari ini & tenant sedang buka. */
    public function scopeSellable(Builder $query): Builder
    {
        return $query->where('is_available_today', true)
            ->whereHas('tenant', fn (Builder $t) => $t->where('status', Tenant::STATUS_ACTIVE)->where('is_open', true));
    }

    public function isSellable(): bool
    {
        return $this->is_available_today && $this->tenant?->canSell();
    }
}

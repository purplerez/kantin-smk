<?php

namespace App\Livewire\Buyer;

use App\Models\Product;
use App\Models\Tenant;
use App\Services\CartService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class TenantMenu extends Component
{
    public Tenant $tenant;

    public function mount(Tenant $tenant): void
    {
        abort_unless($tenant->isActive(), 404);
        $this->tenant = $tenant;
    }

    public function addToCart(int $productId, CartService $cart): void
    {
        $product = Product::with('tenant')->find($productId);

        if (! $product || ! $product->isSellable()) {
            $this->dispatch('toast', message: 'Menu ini sedang tidak tersedia.', tone: 'error');

            return;
        }

        $cart->add($productId);
        $this->dispatch('cart-updated');
        $this->dispatch('toast', message: "{$product->name} ditambahkan ke keranjang");
    }

    public function render()
    {
        return view('livewire.buyer.tenant-menu', [
            'products' => $this->tenant->products()->with('category')->orderByDesc('is_available_today')->orderBy('name')->get(),
            'rating' => $this->tenant->averageRating(),
            'reviews' => $this->tenant->reviews()->with('buyer')->latest()->take(5)->get(),
        ])->title($this->tenant->name.' — KantinSMK Go');
    }
}

<?php

namespace App\Livewire\Buyer;

use App\Models\Category;
use App\Models\Product;
use App\Models\Tenant;
use App\Services\CartService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Beranda — KantinSMK Go')]
class Catalog extends Component
{
    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'kategori')]
    public string $category = '';

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
        $products = Product::query()
            ->with(['tenant', 'category'])
            ->whereHas('tenant', fn ($t) => $t->where('status', Tenant::STATUS_ACTIVE))
            ->when($this->search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$this->search}%")
                ->orWhereHas('tenant', fn ($t) => $t->where('name', 'like', "%{$this->search}%"))))
            ->when($this->category !== '', fn ($q) => $q->whereHas('category', fn ($c) => $c->where('slug', $this->category)))
            ->orderByDesc('is_available_today')
            ->orderBy('name')
            ->get();

        return view('livewire.buyer.catalog', [
            'products' => $products,
            'categories' => Category::orderBy('sort_order')->get(),
            'tenants' => Tenant::where('status', Tenant::STATUS_ACTIVE)->withCount('products')->orderBy('name')->get(),
        ]);
    }
}

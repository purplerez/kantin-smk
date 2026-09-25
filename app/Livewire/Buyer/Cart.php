<?php

namespace App\Livewire\Buyer;

use App\Services\CartService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Keranjang — KantinSMK Go')]
class Cart extends Component
{
    public array $notes = [];

    public function mount(CartService $cart): void
    {
        foreach ($cart->all() as $id => $row) {
            $this->notes[$id] = $row['note'] ?? '';
        }
    }

    public function increment(int $productId, CartService $cart): void
    {
        $cart->add($productId, 1);
        $this->dispatch('cart-updated');
    }

    public function decrement(int $productId, CartService $cart): void
    {
        $qty = ($cart->all()[$productId]['qty'] ?? 1) - 1;
        $cart->setQty($productId, $qty);
        $this->dispatch('cart-updated');
    }

    public function remove(int $productId, CartService $cart): void
    {
        $cart->remove($productId);
        unset($this->notes[$productId]);
        $this->dispatch('cart-updated');
    }

    public function updatedNotes($value, $key): void
    {
        app(CartService::class)->setNote((int) $key, (string) $value);
    }

    public function render(CartService $cart)
    {
        $groups = $cart->groupedByTenant();

        return view('livewire.buyer.cart', [
            'groups' => $groups,
            'total' => $cart->total(),
            'hasUnsellable' => $groups->flatten(1)->contains(fn ($l) => ! $l['sellable']),
        ]);
    }
}

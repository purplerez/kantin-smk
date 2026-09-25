<?php

namespace App\Livewire\Buyer;

use App\Services\CartService;
use Livewire\Attributes\On;
use Livewire\Component;

class CartBadge extends Component
{
    public string $position = 'inline';

    #[On('cart-updated')]
    public function refreshBadge(): void {}

    public function render(CartService $cart)
    {
        return view('livewire.buyer.cart-badge', ['count' => $cart->count()]);
    }
}

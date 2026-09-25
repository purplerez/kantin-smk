<?php

namespace App\Livewire\Buyer;

use App\Enums\PaymentMethod;
use App\Services\CartService;
use App\Services\CheckoutService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Checkout — KantinSMK Go')]
class Checkout extends Component
{
    public string $paymentMethod = 'qris';

    public string $note = '';

    public function mount(CartService $cart)
    {
        if ($cart->lines()->isEmpty()) {
            return $this->redirectRoute('cart', navigate: true);
        }
    }

    public function placeOrder(CheckoutService $checkout)
    {
        $this->validate([
            'paymentMethod' => ['required', 'in:qris,transfer,cash'],
            'note' => ['nullable', 'string', 'max:200'],
        ]);

        $invoice = $checkout->checkout(auth()->user(), PaymentMethod::from($this->paymentMethod), $this->note ?: null);

        $this->dispatch('cart-updated');
        session()->flash('success', 'Pesanan berhasil dibuat. Selesaikan pembayaran ya!');

        return $this->redirectRoute('invoice.show', $invoice, navigate: true);
    }

    public function render(CartService $cart)
    {
        return view('livewire.buyer.checkout', [
            'groups' => $cart->groupedByTenant(),
            'total' => $cart->total(),
            'methods' => PaymentMethod::cases(),
        ]);
    }
}

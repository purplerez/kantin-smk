<?php

namespace App\Livewire\Buyer;

use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Akun — KantinSMK Go')]
class Account extends Component
{
    public function render()
    {
        $user = auth()->user();

        return view('livewire.buyer.account', [
            'user' => $user,
            'totalOrders' => Order::where('buyer_id', $user->id)->count(),
            'totalSpent' => (int) Order::where('buyer_id', $user->id)->where('payment_status', 'paid')->sum('subtotal'),
        ]);
    }
}

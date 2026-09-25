<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Collection;

/**
 * Keranjang persisten di DB (carts + cart_items), dikunci ke buyer_id.
 * Bertahan lintas perangkat & session GC. API tetap sama seperti versi session lama.
 */
class CartService
{
    private ?Cart $cart = null;

    private function buyerId(): ?int
    {
        return auth()->id();
    }

    private function cart(): ?Cart
    {
        if (! $this->buyerId()) {
            return null;
        }

        return $this->cart ??= Cart::firstOrCreate(['buyer_id' => $this->buyerId()]);
    }

    /** [product_id => ['qty' => int, 'note' => string]] — kompatibel dengan pemakaian lama. */
    public function all(): array
    {
        $cart = $this->cart();
        if (! $cart) {
            return [];
        }

        return $cart->items()->get()->mapWithKeys(fn (CartItem $i) => [
            $i->product_id => ['qty' => (int) $i->qty, 'note' => (string) ($i->note ?? '')],
        ])->all();
    }

    public function count(): int
    {
        $cart = $this->cart();

        return $cart ? (int) $cart->items()->sum('qty') : 0;
    }

    public function add(int $productId, int $qty = 1): void
    {
        $cart = $this->cart();
        if (! $cart) {
            return;
        }

        $item = $cart->items()->firstOrNew(['product_id' => $productId]);
        $item->qty = max(1, (int) $item->qty + $qty);
        $item->note ??= '';
        $item->save();
    }

    public function setQty(int $productId, int $qty): void
    {
        $cart = $this->cart();
        if (! $cart) {
            return;
        }

        if ($qty <= 0) {
            $cart->items()->where('product_id', $productId)->delete();

            return;
        }

        $item = $cart->items()->firstOrNew(['product_id' => $productId]);
        $item->qty = $qty;
        $item->note ??= '';
        $item->save();
    }

    public function setNote(int $productId, string $note): void
    {
        $cart = $this->cart();
        if (! $cart) {
            return;
        }

        $cart->items()->where('product_id', $productId)->update(['note' => mb_substr($note, 0, 120)]);
    }

    public function remove(int $productId): void
    {
        $this->setQty($productId, 0);
    }

    public function clear(): void
    {
        $cart = $this->cart();
        if ($cart) {
            $cart->items()->delete();
        }
    }

    /** Baris keranjang lengkap + model produk. Produk yang hilang dibersihkan otomatis. */
    public function lines(): Collection
    {
        $cart = $this->cart();
        if (! $cart) {
            return collect();
        }

        $items = $cart->items()->get();
        if ($items->isEmpty()) {
            return collect();
        }

        $products = Product::with('tenant')->whereIn('id', $items->pluck('product_id'))->get()->keyBy('id');

        $lines = $items->mapWithKeys(function (CartItem $item) use ($products) {
            $product = $products->get($item->product_id);
            if (! $product) {
                return [];
            }

            return [$item->product_id => [
                'product' => $product,
                'qty' => (int) $item->qty,
                'note' => (string) ($item->note ?? ''),
                'line_total' => $product->price * (int) $item->qty,
                'sellable' => $product->isSellable(),
            ]];
        });

        $missing = $items->pluck('product_id')->diff($lines->keys());
        if ($missing->isNotEmpty()) {
            $cart->items()->whereIn('product_id', $missing)->delete();
        }

        return $lines;
    }

    public function groupedByTenant(): Collection
    {
        return $this->lines()->groupBy(fn ($line) => $line['product']->tenant_id);
    }

    public function total(): int
    {
        return (int) $this->lines()->sum('line_total');
    }
}

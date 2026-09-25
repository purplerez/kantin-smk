<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

/**
 * Keranjang berbasis session (server-side) — tidak butuh JavaScript/localStorage.
 * Struktur: session('cart') = [product_id => ['qty' => int, 'note' => string]]
 */
class CartService
{
    private const KEY = 'cart';

    public function all(): array
    {
        return session(self::KEY, []);
    }

    public function count(): int
    {
        return (int) array_sum(array_column($this->all(), 'qty'));
    }

    public function add(int $productId, int $qty = 1): void
    {
        $cart = $this->all();
        $cart[$productId]['qty'] = ($cart[$productId]['qty'] ?? 0) + $qty;
        $cart[$productId]['note'] = $cart[$productId]['note'] ?? '';
        session([self::KEY => $cart]);
    }

    public function setQty(int $productId, int $qty): void
    {
        $cart = $this->all();
        if ($qty <= 0) {
            unset($cart[$productId]);
        } else {
            $cart[$productId]['qty'] = $qty;
            $cart[$productId]['note'] = $cart[$productId]['note'] ?? '';
        }
        session([self::KEY => $cart]);
    }

    public function setNote(int $productId, string $note): void
    {
        $cart = $this->all();
        if (isset($cart[$productId])) {
            $cart[$productId]['note'] = mb_substr($note, 0, 120);
            session([self::KEY => $cart]);
        }
    }

    public function remove(int $productId): void
    {
        $this->setQty($productId, 0);
    }

    public function clear(): void
    {
        session()->forget(self::KEY);
    }

    /**
     * Baris keranjang lengkap dengan model produk, dikelompokkan per tenant.
     * Produk yang sudah dihapus / tidak ada akan dibuang otomatis dari session.
     */
    public function lines(): Collection
    {
        $cart = $this->all();
        if (empty($cart)) {
            return collect();
        }

        $products = Product::with('tenant')->whereIn('id', array_keys($cart))->get()->keyBy('id');

        $lines = collect($cart)->map(function ($row, $id) use ($products) {
            $product = $products->get($id);
            if (! $product) {
                return null;
            }

            return [
                'product' => $product,
                'qty' => (int) $row['qty'],
                'note' => $row['note'] ?? '',
                'line_total' => $product->price * (int) $row['qty'],
                'sellable' => $product->isSellable(),
            ];
        })->filter();

        // Bersihkan produk yang sudah tidak ada
        $missing = array_diff(array_keys($cart), $lines->keys()->all());
        foreach ($missing as $id) {
            $this->remove((int) $id);
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

<?php

namespace App\Livewire\Tenant;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\Role;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\NotificationService;
use App\Services\Realtime;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.panel')]
#[Title('Kasir Counter — Panel Tenant')]
class CounterCashier extends Component
{
    use AuthorizesRequests;

    /**
     * Keranjang POS: ['<product_id>' => qty]
     * Harga selalu diambil fresh dari DB saat save (anti-race).
     */
    public array $cart = [];

    /** Pencarian pembeli (opsional). */
    public string $buyerSearch = '';

    /** ID pembeli terpilih. null = anonim (Pelanggan Umum). */
    public ?int $selectedBuyerId = null;

    public string $paymentMethod = 'cash';

    public string $note = '';

    public function mount(): void
    {
        $user = auth()->user();
        abort_unless($user && $user->tenant && $user->tenant->isActive(), 403);
    }

    // ----------------------------------------------------------------
    // Cart Actions
    // ----------------------------------------------------------------

    public function addToCart(int $productId): void
    {
        $key = (string) $productId;
        $this->cart[$key] = ($this->cart[$key] ?? 0) + 1;
    }

    public function decrementCart(int $productId): void
    {
        $key = (string) $productId;
        $qty = ($this->cart[$key] ?? 0) - 1;
        if ($qty <= 0) {
            unset($this->cart[$key]);
        } else {
            $this->cart[$key] = $qty;
        }
    }

    public function removeFromCart(int $productId): void
    {
        unset($this->cart[(string) $productId]);
    }

    public function clearCart(): void
    {
        $this->cart = [];
    }

    // ----------------------------------------------------------------
    // Buyer Selection
    // ----------------------------------------------------------------

    public function selectBuyer(int $userId): void
    {
        $this->selectedBuyerId = $userId;
        $this->buyerSearch     = '';
    }

    public function selectAnonymous(): void
    {
        $this->selectedBuyerId = null;
        $this->buyerSearch     = '';
    }

    public function clearBuyer(): void
    {
        $this->selectedBuyerId = null;
        $this->buyerSearch     = '';
    }

    // ----------------------------------------------------------------
    // Save (Checkout Counter)
    // ----------------------------------------------------------------

    public function save(NotificationService $notifications): void
    {
        $user   = auth()->user();
        $tenant = $user->tenant;

        abort_unless($tenant && $tenant->isActive(), 403);

        if (empty($this->cart)) {
            throw ValidationException::withMessages(['cart' => 'Keranjang masih kosong.']);
        }

        $this->validate([
            'paymentMethod' => ['required', 'in:cash,qris'],
            'note'          => ['nullable', 'string', 'max:200'],
        ]);

        $method = PaymentMethod::from($this->paymentMethod);

        DB::transaction(function () use ($tenant, $user, $method, $notifications) {
            // Re-validasi produk dari DB (anti-race, anti-client-tampering).
            $productIds = array_map('intval', array_keys($this->cart));
            $products   = Product::whereIn('id', $productIds)
                ->where('is_available_today', true)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $lines      = [];
            $unavailable = [];

            foreach ($this->cart as $productId => $qty) {
                $product = $products->get((int) $productId);
                if (! $product) {
                    $unavailable[] = "ID #{$productId}";
                    continue;
                }
                $qty     = max(1, (int) $qty);
                $lines[] = [
                    'product'    => $product,
                    'qty'        => $qty,
                    'line_total' => $product->price * $qty,
                ];
            }

            if (! empty($unavailable)) {
                throw ValidationException::withMessages([
                    'cart' => 'Beberapa menu tidak tersedia: ' . implode(', ', $unavailable),
                ]);
            }

            if (empty($lines)) {
                throw ValidationException::withMessages(['cart' => 'Keranjang tidak valid.']);
            }

            $total = collect($lines)->sum('line_total');

            // Resolve buyer — anonymous defaults to "Pelanggan Umum"
            $buyer = $this->selectedBuyerId
                ? User::findOrFail($this->selectedBuyerId)
                : $this->resolveGuestUser();

            $invoice = Invoice::create([
                'code'              => Invoice::generateCode(),
                'buyer_id'          => $buyer->id,
                'total'             => $total,
                'payment_method'    => $method,
                'payment_status'    => PaymentStatus::Paid,
                'paid_at'           => now(),
                'note'              => $this->note ?: null,
            ]);

            Payment::create([
                'invoice_id'  => $invoice->id,
                'method'      => $method,
                'status'      => PaymentStatus::Paid,
                'amount'      => $total,
                'paid_at'     => now(),
                'verified_by' => auth()->id(),
            ]);

            $order = Order::create([
                'code'           => Order::generateCode(),
                'invoice_id'     => $invoice->id,
                'buyer_id'       => $buyer->id,
                'tenant_id'      => $tenant->id,
                'status'         => OrderStatus::Completed,
                'payment_status' => PaymentStatus::Paid,
                'subtotal'       => $total,
                'source'         => 'counter',
                'entered_by'     => auth()->id(),
                'paid_at'        => now(),
                'completed_at'   => now(),
                'confirmed_at'   => now(),
                'note'           => $this->note ?: null,
            ]);

            foreach ($lines as $line) {
                OrderItem::create([
                    'order_id'     => $order->id,
                    'product_id'   => $line['product']->id,
                    'product_name' => $line['product']->name,
                    'unit_price'   => $line['product']->price,
                    'qty'          => $line['qty'],
                    'line_total'   => $line['line_total'],
                ]);
            }

            AuditLogger::log('order.counter_entry', $order, [
                'items'  => count($lines),
                'total'  => $total,
                'method' => $method->value,
                'buyer'  => $buyer->name,
            ]);

            // Notifikasi hanya untuk pembeli yang punya akun nyata (bukan anonim)
            if ($this->selectedBuyerId) {
                $notifications->notifyUser((int) $buyer->id, 'payment.received', [
                    'order'  => $order->code,
                    'amount' => $total,
                ]);
            }

            Realtime::orderUpdated($order, 'created');
        });

        $total = collect($this->cart)->reduce(function ($carry, $qty) {
            return $carry; // just for toast — actual total was computed in tx
        }, 0);

        $this->dispatch('toast', message: 'Transaksi counter berhasil disimpan!');
        $this->reset('cart', 'note', 'selectedBuyerId', 'buyerSearch');
        $this->paymentMethod = 'cash';
    }

    // ----------------------------------------------------------------
    // Render
    // ----------------------------------------------------------------

    public function render(): View
    {
        $products = Product::query()
            ->where('is_available_today', true)
            ->with('category')
            ->orderBy('name')
            ->get();

        // Hitung total keranjang dengan harga produk yang sudah di-load
        $productMap = $products->keyBy('id');
        $cartLines  = [];
        $cartTotal  = 0;
        foreach ($this->cart as $productId => $qty) {
            $product = $productMap->get((int) $productId);
            if (! $product) continue;
            $lineTotal   = $product->price * $qty;
            $cartLines[] = compact('product', 'qty', 'lineTotal');
            $cartTotal  += $lineTotal;
        }

        $buyerResults = collect();
        if (strlen(trim($this->buyerSearch)) >= 2) {
            $buyerResults = User::where(function ($q) {
                $q->where('name', 'like', '%' . $this->buyerSearch . '%')
                    ->orWhere('identifier', 'like', '%' . $this->buyerSearch . '%')
                    ->orWhere('email', 'like', '%' . $this->buyerSearch . '%');
            })
                ->where('is_active', true)
                ->where('role', Role::User)
                ->take(6)
                ->get();
        }

        $selectedBuyer = $this->selectedBuyerId
            ? User::find($this->selectedBuyerId)
            : null;

        $recentOrders = Order::where('tenant_id', auth()->user()->tenant_id)
            ->where('source', 'counter')
            ->where('status', OrderStatus::Completed)
            ->with(['buyer', 'items', 'enteredBy'])
            ->latest('completed_at')
            ->take(10)
            ->get();

        return view('livewire.tenant.counter-cashier', [
            'products'      => $products,
            'cartLines'     => $cartLines,
            'cartTotal'     => $cartTotal,
            'buyerResults'  => $buyerResults,
            'selectedBuyer' => $selectedBuyer,
            'recentOrders'  => $recentOrders,
        ]);
    }

    // ----------------------------------------------------------------
    // Helpers
    // ----------------------------------------------------------------

    private function resolveGuestUser(): User
    {
        return User::firstOrCreate(
            ['email' => 'tamu@smkgo.id'],
            [
                'name'       => 'Pelanggan Umum',
                'identifier' => null,
                'password'   => bcrypt('guest123'),
                'role'       => Role::User,
                'is_active'  => true,
            ],
        );
    }
}


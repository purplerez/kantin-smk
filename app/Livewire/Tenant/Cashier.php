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
use App\Models\Scopes\TenantScope;
use App\Models\SpendingClosing;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\NotificationService;
use App\Services\OrderStatusService;
use App\Services\Realtime;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.panel')]
#[Title('Kasir Koperasi — Panel Tenant')]
class Cashier extends Component
{
    use AuthorizesRequests;

    public string $search = '';

    public ?int $selectedUserId = null;

    public ?int $amount = null;

    public string $paymentMethod = 'cash';

    public string $note = '';

    public bool $confirmDuplicate = false;

    public ?string $duplicateWarningMessage = null;

    // Void modal state
    public bool $showVoidModal = false;

    public ?int $voidOrderId = null;

    public string $voidReason = '';

    public function mount(): void
    {
        $user = auth()->user();
        $tenant = $user->tenant;

        if (! $tenant || ! $tenant->is_koperasi) {
            abort(403, 'Fitur kasir cepat hanya tersedia untuk unit Koperasi.');
        }
    }

    public function selectUser(int $userId): void
    {
        $this->selectedUserId = $userId;
        $this->confirmDuplicate = false;
        $this->duplicateWarningMessage = null;
        $this->search = '';
    }

    public function selectGuest(): void
    {
        $guest = User::where('email', 'tamu@smkgo.id')->first()
            ?? User::whereNull('identifier')->where('role', Role::User)->first();

        if (! $guest) {
            $guest = User::create([
                'name' => 'Pelanggan Umum',
                'email' => 'tamu@smkgo.id',
                'identifier' => null,
                'password' => bcrypt('guest123'),
                'role' => Role::User,
                'is_active' => true,
            ]);
        }

        $this->selectUser($guest->id);
    }

    public function clearSelectedUser(): void
    {
        $this->selectedUserId = null;
        $this->confirmDuplicate = false;
        $this->duplicateWarningMessage = null;
    }

    public function verifyAppOrder(int $orderId, OrderStatusService $service): void
    {
        $order = Order::withoutGlobalScope(TenantScope::class)->findOrFail($orderId);
        $this->authorize('verifyPayment', $order);

        $service->markPaid($order);
        $this->dispatch('toast', message: "Pesanan app {$order->code} berhasil diverifikasi lunas!");
    }

    public function save(NotificationService $notifications): void
    {
        $tenant = auth()->user()->tenant;
        abort_unless($tenant && $tenant->is_koperasi, 403);

        $this->validate([
            'selectedUserId' => ['required', 'exists:users,id'],
            'amount' => ['required', 'integer', 'min:1000'],
            'paymentMethod' => ['required', 'in:cash,qris'],
            'note' => ['nullable', 'string', 'max:200'],
        ]);

        $maxAmount = config('koperasi.max_transaction_amount');
        if ($maxAmount !== null && $this->amount > $maxAmount) {
            throw ValidationException::withMessages([
                'amount' => 'Nominal melebihi batas maksimum per transaksi (@rupiah('.$maxAmount.').',
            ]);
        }

        // Cek penutupan bulan
        $period = now('Asia/Jakarta')->format('Y-m');
        $isClosed = SpendingClosing::where('tenant_id', $tenant->id)->where('period', $period)->exists();
        if ($isClosed) {
            throw ValidationException::withMessages([
                'amount' => "Bulan {$period} telah ditutup oleh admin. Tidak dapat mencatat transaksi baru.",
            ]);
        }

        // Cek duplikasi entri dalam 2 menit
        if (! $this->confirmDuplicate) {
            $recent = Order::withoutGlobalScope(TenantScope::class)
                ->where('tenant_id', $tenant->id)
                ->where('buyer_id', $this->selectedUserId)
                ->where('source', 'counter')
                ->where('subtotal', $this->amount)
                ->where('created_at', '>=', now()->subMinutes(2))
                ->latest()
                ->first();

            if ($recent) {
                $this->duplicateWarningMessage = "Peringatan: Ditemukan transaksi kasir dengan nominal sama (@rupiah({$this->amount})) untuk pelanggan ini pada pukul {$recent->created_at->format('H:i')} WIB ({$recent->code}).";
                $this->confirmDuplicate = true;

                return;
            }
        }

        $buyer = User::findOrFail($this->selectedUserId);
        $method = $this->paymentMethod === 'qris' ? PaymentMethod::Qris : PaymentMethod::Cash;

        DB::transaction(function () use ($tenant, $buyer, $method, $notifications) {
            $invoice = Invoice::create([
                'code' => Invoice::generateCode(),
                'buyer_id' => $buyer->id,
                'total' => $this->amount,
                'payment_method' => $method,
                'payment_status' => PaymentStatus::Paid,
                'paid_at' => now(),
                'note' => $this->note ?: null,
            ]);

            Payment::create([
                'invoice_id' => $invoice->id,
                'method' => $method,
                'status' => PaymentStatus::Paid,
                'amount' => $this->amount,
                'paid_at' => now(),
                'verified_by' => auth()->id(),
            ]);

            $order = Order::create([
                'code' => Order::generateCode(),
                'invoice_id' => $invoice->id,
                'buyer_id' => $buyer->id,
                'tenant_id' => $tenant->id,
                'status' => OrderStatus::Completed,
                'payment_status' => PaymentStatus::Paid,
                'subtotal' => $this->amount,
                'source' => 'counter',
                'entered_by' => auth()->id(),
                'paid_at' => now(),
                'completed_at' => now(),
                'note' => $this->note ?: null,
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => null,
                'product_name' => 'Belanja Kasir Koperasi',
                'unit_price' => $this->amount,
                'qty' => 1,
                'line_total' => $this->amount,
                'note' => $this->note ?: null,
            ]);

            AuditLogger::log('order.counter_entry', $order, [
                'amount' => $this->amount,
                'method' => $method->value,
                'buyer' => $buyer->name,
                'nip' => $buyer->identifier,
            ]);

            $notifications->notifyUser((int) $buyer->id, 'payment.received', [
                'order' => $order->code,
                'amount' => $order->subtotal,
            ]);

            Realtime::orderUpdated($order, 'created');
        });

        $this->dispatch('toast', message: "Transaksi kasir @rupiah({$this->amount}) berhasil disimpan!");
        $this->reset('amount', 'note', 'confirmDuplicate', 'duplicateWarningMessage');
    }

    public function openVoidModal(int $orderId): void
    {
        $order = Order::withoutGlobalScope(TenantScope::class)->findOrFail($orderId);
        $this->authorize('voidCounter', $order);

        $this->voidOrderId = $order->id;
        $this->voidReason = '';
        $this->showVoidModal = true;
    }

    public function closeVoidModal(): void
    {
        $this->showVoidModal = false;
        $this->voidOrderId = null;
        $this->voidReason = '';
    }

    public function confirmVoid(): void
    {
        $this->validate([
            'voidReason' => ['required', 'string', 'min:4', 'max:255'],
        ]);

        $order = Order::withoutGlobalScope(TenantScope::class)->findOrFail($this->voidOrderId);
        $this->authorize('voidCounter', $order);

        // Cek apakah bulan order sudah ditutup
        $period = $order->paid_at ? $order->paid_at->setTimezone('Asia/Jakarta')->format('Y-m') : now('Asia/Jakarta')->format('Y-m');
        $isClosed = SpendingClosing::where('tenant_id', $order->tenant_id)->where('period', $period)->exists();
        if ($isClosed) {
            throw ValidationException::withMessages([
                'voidReason' => "Bulan {$period} sudah ditutup oleh admin. Transaksi tidak dapat di-void.",
            ]);
        }

        DB::transaction(function () use ($order) {
            $order->update([
                'status' => OrderStatus::Cancelled,
                'payment_status' => PaymentStatus::Cancelled,
                'cancelled_at' => now(),
                'cancel_reason' => $this->voidReason,
            ]);

            $order->invoice()->update(['payment_status' => PaymentStatus::Cancelled]);
            $order->invoice->payment?->update(['status' => PaymentStatus::Cancelled]);

            AuditLogger::log('order.counter_void', $order, [
                'reason' => $this->voidReason,
                'amount' => $order->subtotal,
            ]);

            Realtime::orderUpdated($order, 'cancelled');
        });

        $this->dispatch('toast', message: "Transaksi {$order->code} berhasil di-void.", tone: 'error');
        $this->closeVoidModal();
    }

    public function render()
    {
        $tenantId = auth()->user()->tenant_id;

        $searchResults = collect();
        if (strlen(trim($this->search)) >= 2) {
            $searchResults = User::where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('identifier', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%');
            })
                ->where('is_active', true)
                ->with('position')
                ->take(6)
                ->get();
        }

        $selectedUser = $this->selectedUserId ? User::with('position')->find($this->selectedUserId) : null;

        $openAppOrders = collect();
        if ($selectedUser) {
            $openAppOrders = Order::withoutGlobalScope(TenantScope::class)
                ->where('tenant_id', $tenantId)
                ->where('buyer_id', $selectedUser->id)
                ->where('source', 'app')
                ->where('payment_status', PaymentStatus::Pending)
                ->where('status', '!=', OrderStatus::Cancelled)
                ->with('items')
                ->latest()
                ->get();
        }

        $recentEntries = Order::withoutGlobalScope(TenantScope::class)
            ->where('tenant_id', $tenantId)
            ->where('source', 'counter')
            ->with(['buyer.position', 'enteredBy'])
            ->latest('created_at')
            ->take(15)
            ->get();

        return view('livewire.tenant.cashier', [
            'searchResults' => $searchResults,
            'selectedUser' => $selectedUser,
            'openAppOrders' => $openAppOrders,
            'recentEntries' => $recentEntries,
        ]);
    }
}


<?php

namespace App\Livewire\Buyer;

use App\Models\Invoice;
use App\Services\CheckoutService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class InvoiceShow extends Component
{
    public Invoice $invoice;

    public function mount(Invoice $invoice): void
    {
        abort_unless($invoice->buyer_id === auth()->id(), 403);
        $this->invoice = $invoice;
    }

    /** DEMO: simulasi callback pembayaran QRIS / transfer. */
    public function simulatePayment(CheckoutService $checkout): void
    {
        $checkout->markInvoicePaid($this->invoice);
        $this->invoice->refresh();
        $this->dispatch('toast', message: 'Pembayaran diterima. Tenant mulai memproses pesananmu!');
    }

    /** Pola QR palsu yang deterministik dari kode invoice (murni SVG server-side, tanpa JS). */
    public function qrCells(): array
    {
        $bits = '';
        foreach (str_split(hash('sha256', $this->invoice->code), 2) as $hex) {
            $bits .= str_pad(decbin(hexdec($hex)), 8, '0', STR_PAD_LEFT);
        }
        $bits .= $bits;

        $cells = [];
        for ($y = 0; $y < 21; $y++) {
            for ($x = 0; $x < 21; $x++) {
                $finder = ($x < 7 && $y < 7) || ($x > 13 && $y < 7) || ($x < 7 && $y > 13);
                if ($finder) {
                    $fx = $x > 13 ? $x - 14 : $x;
                    $fy = $y > 13 ? $y - 14 : $y;
                    $on = $fx === 0 || $fx === 6 || $fy === 0 || $fy === 6 || ($fx >= 2 && $fx <= 4 && $fy >= 2 && $fy <= 4);
                } else {
                    $on = $bits[($y * 21 + $x) % strlen($bits)] === '1';
                }
                if ($on) {
                    $cells[] = [$x, $y];
                }
            }
        }

        return $cells;
    }

    public function render()
    {
        $this->invoice->load('orders.tenant', 'orders.items');

        return view('livewire.buyer.invoice-show')->title('Pembayaran '.$this->invoice->code);
    }
}

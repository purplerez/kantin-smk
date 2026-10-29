<?php

namespace App\Livewire\Buyer;

use App\Services\SpendingTotalCalculator;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Belanja Koperasi — KantinSMK Go')]
class KoperasiSpending extends Component
{
    #[Url]
    public string $month = '';

    public function mount(): void
    {
        if (empty($this->month) || ! preg_match('/^\d{4}-\d{2}$/', $this->month)) {
            $this->month = now('Asia/Jakarta')->format('Y-m');
        }
    }

    public function updatedMonth(): void
    {
        if (! preg_match('/^\d{4}-\d{2}$/', $this->month)) {
            $this->month = now('Asia/Jakarta')->format('Y-m');
        }
    }

    public function previousMonth(): void
    {
        $this->month = Carbon::createFromFormat('!Y-m', $this->month, 'Asia/Jakarta')
            ->subMonth()
            ->format('Y-m');
    }

    public function nextMonth(): void
    {
        $this->month = Carbon::createFromFormat('!Y-m', $this->month, 'Asia/Jakarta')
            ->addMonth()
            ->format('Y-m');
    }

    public function currentMonth(): void
    {
        $this->month = now('Asia/Jakarta')->format('Y-m');
    }

    public function render(SpendingTotalCalculator $calculator)
    {
        $user = auth()->user();
        $koperasi = $calculator->getKoperasiTenant();

        if (! $koperasi) {
            abort(404, 'Tenant koperasi belum dikonfigurasi.');
        }

        $summary = $calculator->calculateForUser($user, $koperasi, $this->month);

        $monthDate = Carbon::createFromFormat('!Y-m', $this->month, 'Asia/Jakarta');
        $monthLabel = $monthDate->translatedFormat('F Y');

        $isCurrentMonth = $this->month === now('Asia/Jakarta')->format('Y-m');

        return view('livewire.buyer.koperasi-spending', [
            'user' => $user,
            'koperasi' => $koperasi,
            'summary' => $summary,
            'monthLabel' => $monthLabel,
            'isCurrentMonth' => $isCurrentMonth,
        ]);
    }
}


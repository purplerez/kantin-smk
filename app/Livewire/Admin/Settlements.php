<?php

namespace App\Livewire\Admin;

use App\Models\TenantSettlement;
use App\Services\AuditLogger;
use App\Services\SettlementService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.panel')]
#[Title('Settlement — Platform')]
class Settlements extends Component
{
    public string $date = '';

    public function mount(): void
    {
        $this->date = now()->toDateString();
    }

    public function generate(SettlementService $service): void
    {
        $service->generateForDate($this->date, auth()->user());
        AuditLogger::log('settlement.generated', null, ['date' => $this->date, 'scope' => 'all']);
        $this->dispatch('toast', message: "Settlement dibuat untuk {$this->date}");
    }

    public function render()
    {
        $settlements = TenantSettlement::with('tenant')
            ->where('date', $this->date)
            ->orderByDesc('gross')
            ->get();

        return view('livewire.admin.settlements', [
            'settlements' => $settlements,
            'commissionPercent' => (float) env('COMMISSION_PERCENT', 0),
            'totals' => [
                'orders' => (int) $settlements->sum('orders_count'),
                'gross' => (int) $settlements->sum('gross'),
                'commission' => (int) $settlements->sum('commission'),
                'net' => (int) $settlements->sum('net'),
            ],
        ]);
    }
}

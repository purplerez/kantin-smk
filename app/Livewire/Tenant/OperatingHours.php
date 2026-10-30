<?php

namespace App\Livewire\Tenant;

use App\Models\TenantHours;
use App\Services\AuditLogger;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.panel')]
#[Title('Jam Operasional — Tenant')]
class OperatingHours extends Component
{
    use AuthorizesRequests;

    /** Indexed by day_of_week (0=Minggu … 6=Sabtu). */
    public array $hours = [];

    public static array $DAY_NAMES = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

    public function mount(): void
    {
        $tenant = auth()->user()->tenant;
        $this->authorize('manage', $tenant);
        $existing = $tenant->hours()->get()->keyBy('day_of_week');

        for ($d = 0; $d <= 6; $d++) {
            $row = $existing->get($d);
            $this->hours[$d] = [
                'is_open'  => $row ? (bool) $row->is_open : true,
                'open_at'  => $row ? substr($row->open_at, 0, 5) : '07:00',
                'close_at' => $row ? substr($row->close_at, 0, 5) : '15:00',
            ];
        }
    }

    public function save(): void
    {
        $this->authorize('manage', auth()->user()->tenant);

        $this->validate([
            'hours.*.is_open'  => ['boolean'],
            'hours.*.open_at'  => ['required', 'date_format:H:i'],
            'hours.*.close_at' => ['required', 'date_format:H:i'],
        ]);

        // Validasi manual: close_at harus setelah open_at untuk hari yang buka
        foreach ($this->hours as $day => $row) {
            if ($row['is_open'] && $row['close_at'] <= $row['open_at']) {
                $this->addError("hours.{$day}.close_at", 'Jam tutup harus setelah jam buka.');
            }
        }

        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }

        $tenant = auth()->user()->tenant;

        foreach ($this->hours as $day => $row) {
            TenantHours::updateOrCreate(
                ['tenant_id' => $tenant->id, 'day_of_week' => $day],
                [
                    'is_open'  => $row['is_open'],
                    'open_at'  => $row['open_at'] . ':00',
                    'close_at' => $row['close_at'] . ':00',
                ],
            );
        }

        AuditLogger::log('tenant.hours_updated', $tenant, $this->hours);
        $this->dispatch('toast', message: 'Jam operasional tersimpan.');
    }

    public function render(): View
    {
        return view('livewire.tenant.operating-hours', [
            'dayNames' => self::$DAY_NAMES,
        ]);
    }
}

<?php

namespace App\Livewire\Tenant;

use App\Models\Position;
use App\Models\SpendingAdjustment;
use App\Models\SpendingClosing;
use App\Models\SpendingClosingItem;
use App\Models\Tenant;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\SpendingTotalCalculator;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.panel')]
#[Title('Rekap Belanja Pegawai — Koperasi')]
class SpendingRecap extends Component
{
    use AuthorizesRequests;

    #[Url]
    public string $month = '';

    public string $search = '';

    public string $statusFilter = '';

    public string $positionFilter = '';

    // Modal Tutup Bulan
    public bool $showCloseModal = false;

    // Modal Penyesuaian
    public bool $showAdjustmentModal = false;

    public ?int $adjUserId = null;

    public ?int $adjAmount = null;

    public string $adjReason = '';

    public string $adjPeriod = '';

    public function mount(): void
    {
        $tenant = auth()->user()->tenant;
        if (! $tenant || ! $tenant->is_koperasi) {
            abort(403, 'Akses rekap belanja hanya untuk pengelola unit Koperasi.');
        }

        $this->authorize('viewSpendingRecap', $tenant);

        if (empty($this->month) || ! preg_match('/^\d{4}-\d{2}$/', $this->month)) {
            $this->month = now('Asia/Jakarta')->format('Y-m');
        }

        $this->adjPeriod = now('Asia/Jakarta')->format('Y-m');
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

    public function openCloseModal(): void
    {
        $tenant = auth()->user()->tenant;
        $this->authorize('manageSpending', $tenant);
        $this->showCloseModal = true;
    }

    public function closeCloseModal(): void
    {
        $this->showCloseModal = false;
    }

    public function executeCloseMonth(SpendingTotalCalculator $calculator): void
    {
        $tenant = auth()->user()->tenant;
        $this->authorize('manageSpending', $tenant);

        $isClosed = SpendingClosing::where('tenant_id', $tenant->id)
            ->where('period', $this->month)
            ->exists();

        if ($isClosed) {
            throw ValidationException::withMessages([
                'close' => "Periode {$this->month} sudah ditutup sebelumnya.",
            ]);
        }

        DB::transaction(function () use ($tenant, $calculator) {
            $closing = SpendingClosing::create([
                'tenant_id' => $tenant->id,
                'period' => $this->month,
                'closed_at' => now(),
                'closed_by' => auth()->id(),
            ]);

            // Ambil seluruh pegawai (users dengan position_id)
            $employees = User::whereNotNull('position_id')
                ->where('is_active', true)
                ->with('position')
                ->get();

            foreach ($employees as $emp) {
                $calc = $calculator->calculateForUser($emp, $tenant, $this->month);

                SpendingClosingItem::create([
                    'closing_id' => $closing->id,
                    'user_id' => $emp->id,
                    'position_id' => $emp->position_id,
                    'target_amount' => $calc['target'],
                    'total_amount' => $calc['total_verified'],
                    'is_met' => $calc['is_met'],
                ]);
            }

            AuditLogger::log('spending.month_closed', $closing, [
                'period' => $this->month,
                'employees_count' => $employees->count(),
            ]);
        });

        $this->showCloseModal = false;
        $this->dispatch('toast', message: "Buku belanja periode {$this->month} berhasil ditutup & dikunci.");
    }

    public function openAdjustmentModal(?int $userId = null): void
    {
        $tenant = auth()->user()->tenant;
        $this->authorize('manageSpending', $tenant);

        $this->adjUserId = $userId;
        $this->adjAmount = null;
        $this->adjReason = '';
        $this->adjPeriod = now('Asia/Jakarta')->format('Y-m');
        $this->showAdjustmentModal = true;
    }

    public function closeAdjustmentModal(): void
    {
        $this->showAdjustmentModal = false;
        $this->adjUserId = null;
        $this->adjAmount = null;
        $this->adjReason = '';
    }

    public function saveAdjustment(): void
    {
        $tenant = auth()->user()->tenant;
        $this->authorize('manageSpending', $tenant);

        $this->validate([
            'adjUserId' => ['required', 'exists:users,id'],
            'adjAmount' => ['required', 'integer', 'not_in:0'],
            'adjReason' => ['required', 'string', 'min:4', 'max:255'],
            'adjPeriod' => ['required', 'regex:/^\d{4}-\d{2}$/'],
        ]);

        $user = User::findOrFail($this->adjUserId);

        DB::transaction(function () use ($tenant, $user) {
            $adj = SpendingAdjustment::create([
                'user_id' => $user->id,
                'tenant_id' => $tenant->id,
                'period' => $this->adjPeriod,
                'amount' => $this->adjAmount,
                'reason' => $this->adjReason,
                'created_by' => auth()->id(),
            ]);

            AuditLogger::log('spending.adjustment_created', $adj, [
                'user' => $user->name,
                'period' => $this->adjPeriod,
                'amount' => $this->adjAmount,
                'reason' => $this->adjReason,
            ]);
        });

        $this->closeAdjustmentModal();
        $this->dispatch('toast', message: "Penyesuaian belanja untuk {$user->name} berhasil disimpan.");
    }

    public function exportCsv(SpendingTotalCalculator $calculator)
    {
        $tenant = auth()->user()->tenant;
        $this->authorize('viewSpendingRecap', $tenant);

        $monthStr = $this->month;
        $filename = "rekap-belanja-koperasi-{$monthStr}.csv";

        return response()->streamDownload(function () use ($tenant, $calculator, $monthStr) {
            $handle = fopen('php://output', 'w');
            // BOM UTF-8 agar Excel membuka karakter dengan benar
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'No',
                'NIP / Identitas',
                'Nama Pegawai',
                'Jabatan',
                'Total Belanja (Rp)',
                'Target (Rp)',
                'Sisa Kekurangan (Rp)',
                'Status Pemenuhan',
            ]);

            $employees = User::whereNotNull('position_id')
                ->where('is_active', true)
                ->with('position')
                ->orderBy('name')
                ->get();

            $no = 1;
            foreach ($employees as $emp) {
                $calc = $calculator->calculateForUser($emp, $tenant, $monthStr);
                fputcsv($handle, [
                    $no++,
                    $emp->identifier ?: '-',
                    $emp->name,
                    $calc['position_name'],
                    $calc['total_verified'],
                    $calc['target'],
                    $calc['remaining'],
                    $calc['is_met'] ? 'TERPENUHI' : 'BELUM TERPENUHI',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function render(SpendingTotalCalculator $calculator)
    {
        $tenant = auth()->user()->tenant;

        // Cek status tutup buku periode terpilih
        $closing = SpendingClosing::where('tenant_id', $tenant->id)
            ->where('period', $this->month)
            ->first();

        // Ambil daftar pegawai
        $employeesQuery = User::whereNotNull('position_id')
            ->where('is_active', true)
            ->with('position')
            ->when($this->search !== '', function ($q) {
                $q->where(function ($w) {
                    $w->where('name', 'like', "%{$this->search}%")
                        ->orWhere('identifier', 'like', "%{$this->search}%");
                });
            })
            ->when($this->positionFilter !== '', function ($q) {
                $q->where('position_id', $this->positionFilter);
            })
            ->orderBy('name');

        $employees = $employeesQuery->get();

        // Hitung status belanja tiap pegawai
        $recapList = $employees->map(function (User $emp) use ($tenant, $calculator) {
            $calc = $calculator->calculateForUser($emp, $tenant, $this->month);

            return [
                'user' => $emp,
                'calc' => $calc,
            ];
        });

        // Filter status pemenuhan jika dipilih
        if ($this->statusFilter === 'met') {
            $recapList = $recapList->filter(fn ($item) => $item['calc']['is_met']);
        } elseif ($this->statusFilter === 'unmet') {
            $recapList = $recapList->filter(fn ($item) => ! $item['calc']['is_met']);
        }

        // Agregat statistik
        $totalEmployees = $employees->count();
        $metCount = $employees->filter(function ($emp) use ($tenant, $calculator) {
            return $calculator->calculateForUser($emp, $tenant, $this->month)['is_met'];
        })->count();

        $unmetCount = $totalEmployees - $metCount;

        $monthDate = Carbon::createFromFormat('!Y-m', $this->month, 'Asia/Jakarta');
        $monthLabel = $monthDate->translatedFormat('F Y');

        $positions = Position::where('is_active', true)->orderBy('name')->get();
        $allActiveEmployees = User::whereNotNull('position_id')->where('is_active', true)->orderBy('name')->get();

        return view('livewire.tenant.spending-recap', [
            'tenant' => $tenant,
            'closing' => $closing,
            'recapList' => $recapList,
            'totalEmployees' => $totalEmployees,
            'metCount' => $metCount,
            'unmetCount' => $unmetCount,
            'monthLabel' => $monthLabel,
            'positions' => $positions,
            'allActiveEmployees' => $allActiveEmployees,
            'isCurrentMonth' => $this->month === now('Asia/Jakarta')->format('Y-m'),
        ]);
    }
}


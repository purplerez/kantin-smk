<?php

namespace App\Livewire\Tenant;

use App\Models\Position;
use App\Models\SpendingTarget;
use App\Services\AuditLogger;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.panel')]
#[Title('Kelola Jabatan & Target — Koperasi')]
class SpendingSettings extends Component
{
    use AuthorizesRequests;

    // Form Tambah Jabatan
    public bool $showPositionModal = false;

    public string $positionName = '';

    // Form Tambah Target
    public bool $showTargetModal = false;

    public ?int $targetPositionId = null;

    public ?int $targetAmount = null;

    public string $targetEffectiveFrom = '';

    public ?string $targetEffectiveTo = null;

    public function mount(): void
    {
        $tenant = auth()->user()->tenant;
        if (! $tenant || ! $tenant->is_koperasi) {
            abort(403, 'Akses kelola jabatan & target hanya untuk unit Koperasi.');
        }

        $this->authorize('manageSpending', $tenant);

        $this->targetEffectiveFrom = now('Asia/Jakarta')->startOfMonth()->toDateString();
    }

    public function openPositionModal(): void
    {
        $this->positionName = '';
        $this->showPositionModal = true;
    }

    public function closePositionModal(): void
    {
        $this->showPositionModal = false;
        $this->positionName = '';
    }

    public function savePosition(): void
    {
        $tenant = auth()->user()->tenant;
        $this->authorize('manageSpending', $tenant);

        $this->validate([
            'positionName' => ['required', 'string', 'max:80', 'unique:positions,name'],
        ]);

        $pos = Position::create([
            'name' => trim($this->positionName),
            'is_active' => true,
        ]);

        AuditLogger::log('position.created', $pos, ['name' => $pos->name]);

        $this->closePositionModal();
        $this->dispatch('toast', message: "Jabatan '{$pos->name}' berhasil ditambahkan.");
    }

    public function togglePositionActive(int $id): void
    {
        $tenant = auth()->user()->tenant;
        $this->authorize('manageSpending', $tenant);

        $pos = Position::findOrFail($id);
        $pos->update(['is_active' => ! $pos->is_active]);

        AuditLogger::log('position.status_toggled', $pos, ['is_active' => $pos->is_active]);
        $this->dispatch('toast', message: "Status jabatan '{$pos->name}' diperbarui.");
    }

    public function openTargetModal(): void
    {
        $this->targetPositionId = null;
        $this->targetAmount = null;
        $this->targetEffectiveFrom = now('Asia/Jakarta')->startOfMonth()->toDateString();
        $this->targetEffectiveTo = null;
        $this->showTargetModal = true;
    }

    public function closeTargetModal(): void
    {
        $this->showTargetModal = false;
    }

    public function saveTarget(): void
    {
        $tenant = auth()->user()->tenant;
        $this->authorize('manageSpending', $tenant);

        $this->validate([
            'targetPositionId' => ['required', 'exists:positions,id'],
            'targetAmount' => ['required', 'integer', 'min:1000'],
            'targetEffectiveFrom' => ['required', 'date'],
            'targetEffectiveTo' => ['nullable', 'date', 'after_or_equal:targetEffectiveFrom'],
        ]);

        $target = SpendingTarget::create([
            'tenant_id' => $tenant->id,
            'position_id' => $this->targetPositionId,
            'amount' => $this->targetAmount,
            'effective_from' => $this->targetEffectiveFrom,
            'effective_to' => $this->targetEffectiveTo ?: null,
            'created_by' => auth()->id(),
        ]);

        AuditLogger::log('target.created', $target, [
            'position_id' => $this->targetPositionId,
            'amount' => $this->targetAmount,
            'effective_from' => $this->targetEffectiveFrom,
        ]);

        $this->closeTargetModal();
        $this->dispatch('toast', message: 'Target belanja baru berhasil disimpan.');
    }

    public function render()
    {
        $tenant = auth()->user()->tenant;

        $positions = Position::withCount('users')
            ->orderBy('name')
            ->get();

        $targets = SpendingTarget::where('tenant_id', $tenant->id)
            ->with(['position', 'creator'])
            ->orderByDesc('effective_from')
            ->orderByDesc('id')
            ->get();

        return view('livewire.tenant.spending-settings', [
            'positions' => $positions,
            'targets' => $targets,
        ]);
    }
}


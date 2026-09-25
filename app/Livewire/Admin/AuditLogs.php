<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.panel')]
#[Title('Audit Log — Platform')]
class AuditLogs extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.admin.audit-logs', [
            'logs' => AuditLog::with('actor')
                ->when($this->search !== '', fn ($q) => $q->where(fn ($w) => $w
                    ->where('action', 'like', "%{$this->search}%")
                    ->orWhere('subject_type', 'like', "%{$this->search}%")
                    ->orWhereHas('actor', fn ($a) => $a->where('name', 'like', "%{$this->search}%"))))
                ->latest('id')
                ->paginate(30),
        ]);
    }
}

<?php

namespace App\Livewire\Admin;

use App\Models\Tenant;
use App\Services\AuditLogger;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.panel')]
#[Title('Kelola Tenant — Platform')]
class Tenants extends Component
{
    public bool $showForm = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $description = '';

    public string $image_url = '';

    public string $bank_name = '';

    public string $bank_account = '';

    public string $bank_holder = '';

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:80', Rule::unique('tenants', 'name')->ignore($this->editingId)],
            'description' => ['nullable', 'string', 'max:300'],
            'image_url' => ['nullable', 'url', 'max:500'],
            'bank_name' => ['nullable', 'string', 'max:40'],
            'bank_account' => ['nullable', 'string', 'max:40'],
            'bank_holder' => ['nullable', 'string', 'max:80'],
        ];
    }

    public function create(): void
    {
        $this->reset('editingId', 'name', 'description', 'image_url', 'bank_name', 'bank_account', 'bank_holder');
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $tenant = Tenant::findOrFail($id);
        $this->editingId = $tenant->id;
        foreach (['name', 'description', 'image_url', 'bank_name', 'bank_account', 'bank_holder'] as $f) {
            $this->{$f} = (string) $tenant->{$f};
        }
        $this->showForm = true;
    }

    public function save(): void
    {
        $data = $this->validate();

        if ($this->editingId) {
            $tenant = Tenant::findOrFail($this->editingId);
            $tenant->update($data);
            AuditLogger::log('tenant.updated', $tenant, $data);
        } else {
            $tenant = Tenant::create($data + ['slug' => Str::slug($data['name']), 'status' => Tenant::STATUS_PENDING]);
            AuditLogger::log('tenant.created', $tenant, $data);
        }

        $this->showForm = false;
        $this->dispatch('toast', message: 'Tenant tersimpan.');
    }

    public function setStatus(int $id, string $status): void
    {
        abort_unless(in_array($status, [Tenant::STATUS_ACTIVE, Tenant::STATUS_SUSPENDED, Tenant::STATUS_PENDING], true), 422);
        $tenant = Tenant::findOrFail($id);
        $tenant->update(['status' => $status]);
        AuditLogger::log('tenant.status', $tenant, ['status' => $status]);
        $this->dispatch('toast', message: "{$tenant->name}: {$status}");
    }

    public function render()
    {
        return view('livewire.admin.tenants', [
            'tenants' => Tenant::withCount(['products', 'staff', 'orders'])->orderByRaw("CASE status WHEN 'pending' THEN 0 WHEN 'active' THEN 1 ELSE 2 END")->orderBy('name')->get(),
        ]);
    }
}

<?php

namespace App\Livewire\Admin;

use App\Enums\Role;
use App\Models\Tenant;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.panel')]
#[Title('Kelola Pengguna — Platform')]
class Users extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $roleFilter = '';

    public bool $showForm = false;

    public bool $showImport = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $email = '';

    public string $identifier = '';

    public string $role = 'user';

    public ?int $tenant_id = null;

    public string $password = '';

    public string $importText = '';

    public array $importResult = [];

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:120', Rule::unique('users', 'email')->ignore($this->editingId)],
            'identifier' => ['nullable', 'string', 'max:30', Rule::unique('users', 'identifier')->ignore($this->editingId)],
            'role' => ['required', new Enum(Role::class)],
            'tenant_id' => [Rule::requiredIf(fn () => Role::from($this->role)->isTenant()), 'nullable', 'exists:tenants,id'],
            'password' => [$this->editingId ? 'nullable' : 'required', 'string', 'min:6', 'max:64'],
        ];
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedRoleFilter(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->reset('editingId', 'name', 'email', 'identifier', 'tenant_id', 'password');
        $this->role = 'user';
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $user = User::findOrFail($id);
        $this->editingId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->identifier = (string) $user->identifier;
        $this->role = $user->role->value;
        $this->tenant_id = $user->tenant_id;
        $this->password = '';
        $this->showForm = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        $data['email'] = mb_strtolower($data['email']);
        $data['identifier'] = $data['identifier'] ?: null;
        if (! Role::from($data['role'])->isTenant()) {
            $data['tenant_id'] = null;
        }
        if (empty($data['password'])) {
            unset($data['password']);
        }

        if ($this->editingId) {
            $user = User::findOrFail($this->editingId);
            $user->update($data);
            AuditLogger::log('user.updated', $user, ['role' => $data['role'], 'password_reset' => isset($data['password'])]);
        } else {
            $user = User::create($data + ['is_active' => true]);
            AuditLogger::log('user.provisioned', $user, ['role' => $data['role']]);
        }

        $this->showForm = false;
        $this->dispatch('toast', message: 'Akun tersimpan.');
    }

    public function toggleActive(int $id): void
    {
        $user = User::findOrFail($id);
        abort_if($user->id === auth()->id(), 422, 'Tidak bisa menonaktifkan akun sendiri.');
        $user->update(['is_active' => ! $user->is_active]);
        AuditLogger::log('user.toggle_active', $user, ['is_active' => $user->is_active]);
    }

    /**
     * Provisioning massal pembeli: satu baris per akun, format
     * Nama Lengkap;email@sekolah.id;NIS/NIP;password(opsional)
     */
    public function import(): void
    {
        $this->validate(['importText' => ['required', 'string']]);
        $created = 0;
        $skipped = [];

        foreach (preg_split('/\r?\n/', trim($this->importText)) as $lineNo => $line) {
            $parts = array_map('trim', explode(';', $line));
            if (count($parts) < 2 || ! filter_var($parts[1], FILTER_VALIDATE_EMAIL)) {
                $skipped[] = 'Baris '.($lineNo + 1).': format tidak valid';

                continue;
            }
            [$name, $email] = $parts;
            $email = mb_strtolower($email);
            if (User::where('email', $email)->exists()) {
                $skipped[] = "Baris ".($lineNo + 1).": {$email} sudah ada";

                continue;
            }
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'identifier' => $parts[2] ?? null,
                'password' => $parts[3] ?? Str::password(10, symbols: false),
                'role' => Role::User,
                'is_active' => true,
            ]);
            AuditLogger::log('user.provisioned', $user, ['role' => 'user', 'bulk' => true]);
            $created++;
        }

        $this->importResult = ['created' => $created, 'skipped' => $skipped];
        $this->importText = '';
        $this->dispatch('toast', message: "{$created} akun pembeli dibuat");
    }

    public function render()
    {
        return view('livewire.admin.users', [
            'users' => User::with('tenant')
                ->when($this->search !== '', fn ($q) => $q->where(fn ($w) => $w
                    ->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%")
                    ->orWhere('identifier', 'like', "%{$this->search}%")))
                ->when($this->roleFilter !== '', fn ($q) => $q->where('role', $this->roleFilter))
                ->orderBy('name')
                ->paginate(15),
            'tenants' => Tenant::orderBy('name')->get(),
            'roles' => Role::cases(),
        ]);
    }
}

<?php

namespace App\Livewire\Auth;

use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Onboarding admin-provisioned: set password lewat token sekali-pakai.
 * Token disimpan sebagai HASH di password_reset_tokens, kedaluwarsa (default 48 jam).
 */
#[Layout('components.layouts.base')]
#[Title('Aktivasi Akun — KantinSMK Go')]
class SetPassword extends Component
{
    public string $token = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public bool $valid = false;

    public ?User $target = null;

    public function mount(string $token): void
    {
        $this->token = $token;
        $this->email = mb_strtolower(trim((string) request()->query('email', '')));
        $this->validateToken();
    }

    private function ttlHours(): int
    {
        return (int) env('ONBOARD_TOKEN_TTL_HOURS', 48);
    }

    private function validateToken(): void
    {
        $row = DB::table('password_reset_tokens')->where('email', $this->email)->first();

        if (! $row || ! Hash::check($this->token, $row->token)) {
            $this->valid = false;

            return;
        }

        if ($row->created_at && Carbon::parse($row->created_at)->addHours($this->ttlHours())->isPast()) {
            // Token kedaluwarsa: buang agar tidak bisa dipakai lagi.
            DB::table('password_reset_tokens')->where('email', $this->email)->delete();
            $this->valid = false;

            return;
        }

        $this->target = User::where('email', $this->email)->first();
        $this->valid = (bool) $this->target;
    }

    public function submit()
    {
        if (! $this->valid || ! $this->target) {
            return;
        }

        $this->validate([
            'password' => ['required', 'string', 'min:8', 'max:64', 'confirmed'],
        ], [
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Password minimal 8 karakter.',
        ]);

        $user = $this->target;
        $user->update([
            'password' => $this->password,
            'must_change_password' => false,
            'is_active' => true,
        ]);

        // Single-use: token dihapus setelah dipakai.
        DB::table('password_reset_tokens')->where('email', $this->email)->delete();
        AuditLogger::log('onboard.completed', $user);

        Auth::login($user);
        request()->session()->regenerate();

        return redirect()->route($user->role->homeRoute());
    }

    public function render()
    {
        return view('livewire.auth.set-password');
    }
}

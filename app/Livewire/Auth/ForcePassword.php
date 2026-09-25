<?php

namespace App\Livewire\Auth;

use App\Services\AuditLogger;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Dipaksa saat first-login untuk akun provisioned yang punya must_change_password=true.
 */
#[Layout('components.layouts.base')]
#[Title('Ganti Password — KantinSMK Go')]
class ForcePassword extends Component
{
    public string $password = '';

    public string $password_confirmation = '';

    public function submit()
    {
        $this->validate([
            'password' => ['required', 'string', 'min:8', 'max:64', 'confirmed'],
        ], [
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Password minimal 8 karakter.',
        ]);

        $user = auth()->user();
        $user->update(['password' => $this->password, 'must_change_password' => false]);
        AuditLogger::log('password.changed', $user);

        return redirect()->route($user->role->homeRoute());
    }

    public function render()
    {
        return view('livewire.auth.force-password');
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pemakaian: ->middleware('role:admin') atau ->middleware('role:tenant_admin,tenant_staf')
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! $user->is_active) {
            auth()->logout();

            return redirect()->route('login')->withErrors(['email' => 'Akun Anda dinonaktifkan.']);
        }

        // Onboarding: paksa set/ganti password sebelum boleh mengakses aplikasi.
        if ($user->must_change_password) {
            return redirect()->route('password.change');
        }

        if (! in_array($user->role->value, $roles, true)) {
            return redirect()->route($user->role->homeRoute())
                ->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
        }

        if ($user->isTenantRole() && ! $user->tenant?->isActive()) {
            abort(403, 'Tenant Anda belum aktif atau sedang ditangguhkan.');
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PastikanPenggunaAktif
{
    public function handle(Request $permintaan, Closure $lanjut): Response
    {
        if (! $permintaan->user()?->status || ! $permintaan->user()?->role?->status) {
            Auth::logout();
            $permintaan->session()->invalidate();
            $permintaan->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['email' => 'Akun atau role Anda tidak aktif.']);
        }

        return $lanjut($permintaan);
    }
}

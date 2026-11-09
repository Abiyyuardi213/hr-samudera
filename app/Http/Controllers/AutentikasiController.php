<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AutentikasiController extends Controller
{
    public function formulir(): View
    {
        return view('autentikasi.login');
    }

    public function masuk(Request $permintaan): RedirectResponse
    {
        $data = $permintaan->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], ['required' => ':attribute wajib diisi.', 'email' => 'Format email tidak valid.']);
        $kunci = Str::lower($data['email']).'|'.$permintaan->ip();

        if (RateLimiter::tooManyAttempts($kunci, 5)) {
            throw ValidationException::withMessages(['email' => 'Terlalu banyak percobaan. Coba kembali dalam '.RateLimiter::availableIn($kunci).' detik.']);
        }

        if (! Auth::attemptWhen([...$data, 'status' => true], fn ($pengguna): bool => (bool) $pengguna->role?->status)) {
            RateLimiter::hit($kunci, 60);
            throw ValidationException::withMessages(['email' => 'Email atau password salah, atau akun dan role tidak aktif.']);
        }

        RateLimiter::clear($kunci);
        $permintaan->session()->regenerate();

        return redirect()->intended(route('beranda'));
    }

    public function keluar(Request $permintaan): RedirectResponse
    {
        Auth::logout();
        $permintaan->session()->invalidate();
        $permintaan->session()->regenerateToken();

        return redirect()->route('login')->with('pesan', 'Anda berhasil keluar.');
    }
}

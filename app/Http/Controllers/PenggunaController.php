<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PenggunaController extends Controller
{
    public function daftar(): View
    {
        $daftarPengguna = User::query()->with('role')->orderBy('name')->paginate(15);

        return view('pengguna.daftar', compact('daftarPengguna'));
    }

    public function tambah(): View
    {
        $daftarRole = Role::query()->where('status', true)->orderBy('nama')->get();

        return view('pengguna.tambah', compact('daftarRole'));
    }

    public function simpan(Request $permintaan): RedirectResponse
    {
        $data = $this->validasi($permintaan);
        if ($permintaan->hasFile('foto_profil')) {
            $data['foto_profil'] = $permintaan->file('foto_profil')->store('foto-profil', 'public');
        }
        User::query()->create($data);

        return redirect()->route('pengguna.daftar')->with('pesan', 'Pengguna berhasil ditambahkan.');
    }

    public function tampilkan(User $pengguna): View
    {
        $pengguna->load('role');

        return view('pengguna.tampilkan', compact('pengguna'));
    }

    public function ubah(User $pengguna): View
    {
        $daftarRole = Role::query()->where('status', true)->orWhere('id', $pengguna->role_id)->orderBy('nama')->get();

        return view('pengguna.ubah', compact('pengguna', 'daftarRole'));
    }

    public function perbarui(Request $permintaan, User $pengguna): RedirectResponse
    {
        $data = $this->validasi($permintaan, $pengguna);
        if ((int) $permintaan->user()->id === (int) $pengguna->id) {
            $permintaan->validate([
                'status' => [Rule::in([1])],
                'role_id' => [Rule::in([$pengguna->role_id])],
            ], ['in' => 'Anda tidak dapat menonaktifkan akun atau mengganti role sendiri.']);
        }
        if (empty($data['password'])) {
            unset($data['password']);
        }
        $fotoLama = $pengguna->foto_profil;
        if ($permintaan->hasFile('foto_profil')) {
            $data['foto_profil'] = $permintaan->file('foto_profil')->store('foto-profil', 'public');
        } else {
            unset($data['foto_profil']);
        }
        $pengguna->update($data);
        if ($permintaan->hasFile('foto_profil') && $fotoLama) {
            Storage::disk('public')->delete($fotoLama);
        }

        return redirect()->route('pengguna.daftar')->with('pesan', 'Pengguna berhasil diperbarui.');
    }

    public function hapus(Request $permintaan, User $pengguna): RedirectResponse
    {
        if ((int) $permintaan->user()->id === (int) $pengguna->id) {
            return back()->withErrors(['pengguna' => 'Anda tidak dapat menghapus akun sendiri.']);
        }
        $fotoLama = $pengguna->foto_profil;
        $pengguna->delete();
        if ($fotoLama) {
            Storage::disk('public')->delete($fotoLama);
        }

        return redirect()->route('pengguna.daftar')->with('pesan', 'Pengguna berhasil dihapus.');
    }

    /** @return array<string, mixed> */
    private function validasi(Request $permintaan, ?User $pengguna = null): array
    {
        $data = $permintaan->validate([
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($pengguna)],
            'role_id' => ['required', 'uuid', Rule::exists('role', 'id')->where('status', true)],
            'status' => ['required', 'boolean'],
            'password' => [$pengguna ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'foto_profil' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'max' => ':attribute melebihi batas yang diperbolehkan.',
            'email' => 'Format email tidak valid.',
            'unique' => 'Email sudah digunakan.',
            'exists' => 'Pilih role yang aktif.',
            'uuid' => 'Role tidak valid.',
            'boolean' => 'Status tidak valid.',
            'min' => 'Password minimal 8 karakter.',
            'confirmed' => 'Konfirmasi password tidak sesuai.',
            'image' => 'Foto profil harus berupa gambar.',
            'mimes' => 'Foto profil harus berformat JPG, PNG, atau WebP.',
        ], ['role_id' => 'role', 'foto_profil' => 'foto profil']);
        $data['name'] = $data['nama'];
        unset($data['nama']);

        return $data;
    }
}

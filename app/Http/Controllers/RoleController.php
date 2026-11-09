<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function daftar(): View
    {
        $daftarRole = Role::query()->orderBy('nama')->paginate(15);

        return view('role.daftar', compact('daftarRole'));
    }

    public function tambah(): View
    {
        return view('role.tambah');
    }

    public function simpan(Request $permintaan): RedirectResponse
    {
        $data = $this->validasi($permintaan);
        Role::query()->create($data);

        return redirect()->route('role.daftar')->with('pesan', 'Role berhasil ditambahkan.');
    }

    public function tampilkan(Role $role): View
    {
        return view('role.tampilkan', compact('role'));
    }

    public function ubah(Role $role): View
    {
        return view('role.ubah', compact('role'));
    }

    public function perbarui(Request $permintaan, Role $role): RedirectResponse
    {
        if ($role->id === $permintaan->user()->role_id) {
            return back()->withErrors(['role' => 'Role yang sedang Anda gunakan tidak dapat diubah.']);
        }

        $data = $this->validasi($permintaan, true);
        $role->update($data);

        return redirect()->route('role.daftar')->with('pesan', 'Role berhasil diperbarui.');
    }

    public function hapus(Role $role): RedirectResponse
    {
        if ($role->pengguna()->exists()) {
            return back()->withErrors(['role' => 'Role masih digunakan oleh pengguna dan tidak dapat dihapus.']);
        }

        $role->delete();

        return redirect()->route('role.daftar')->with('pesan', 'Role berhasil dihapus.');
    }

    /** @return array{nama?: string, deskripsi?: string|null, status?: bool|int|string} */
    private function validasi(Request $permintaan, bool $pembaruan = false): array
    {
        return $permintaan->validate([
            'nama' => [$pembaruan ? 'sometimes' : 'required', 'required', 'string', 'max:255'],
            'deskripsi' => ['sometimes', 'nullable', 'string'],
            'status' => ['sometimes', 'required', 'boolean'],
        ], [
            'nama.required' => 'Nama role wajib diisi.',
            'nama.string' => 'Nama role harus berupa teks.',
            'nama.max' => 'Nama role maksimal 255 karakter.',
            'deskripsi.string' => 'Deskripsi harus berupa teks.',
            'status.required' => 'Status wajib diisi.',
            'status.boolean' => 'Status harus bernilai true atau false.',
        ]);
    }
}

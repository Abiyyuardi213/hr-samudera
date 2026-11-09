<?php

namespace App\Http\Controllers;

use App\Models\StatusPegawai;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StatusPegawaiController extends Controller
{
    public function daftar(Request $permintaan): View
    {
        $data = $permintaan->validate(['cari' => ['nullable', 'string', 'max:100'], 'ubah' => ['nullable', 'uuid']]);
        $pencarian = $data['cari'] ?? '';
        $daftarStatus = StatusPegawai::query()->when($pencarian !== '', function ($kueri) use ($pencarian): void {
            $kueri->where(function ($kueri) use ($pencarian): void {
                $kueri->where('nama', 'like', '%'.$pencarian.'%')->orWhere('kode', 'like', '%'.$pencarian.'%');
            });
        })->orderBy('nama')->paginate(15)->appends(['cari' => $pencarian]);
        $statusPegawai = ! empty($data['ubah']) ? StatusPegawai::query()->findOrFail($data['ubah']) : null;

        return view('status-pegawai.daftar', compact('daftarStatus', 'pencarian', 'statusPegawai'));
    }

    public function tambah(): RedirectResponse
    {
        return redirect()->route('status-pegawai.daftar', ['tambah' => 1]);
    }

    public function simpan(Request $permintaan): RedirectResponse
    {
        $data = $this->validasi($permintaan);
        retry(3, fn () => StatusPegawai::query()->create($data), 0, fn (\Exception $kesalahan): bool => $kesalahan instanceof UniqueConstraintViolationException);

        return redirect()->route('status-pegawai.daftar')->with('pesan', 'Status pegawai berhasil ditambahkan.');
    }

    public function ubah(StatusPegawai $statusPegawai): RedirectResponse
    {
        return redirect()->route('status-pegawai.daftar', ['ubah' => $statusPegawai->id]);
    }

    public function perbarui(Request $permintaan, StatusPegawai $statusPegawai): RedirectResponse
    {
        $statusPegawai->update($this->validasi($permintaan));

        return redirect()->route('status-pegawai.daftar')->with('pesan', 'Status pegawai berhasil diperbarui.');
    }

    public function hapus(StatusPegawai $statusPegawai): RedirectResponse
    {
        if ($statusPegawai->pegawai()->exists() || $statusPegawai->riwayatPenempatan()->exists()) {
            return back()->withErrors(['pegawai' => 'Status pegawai masih digunakan oleh pegawai atau riwayat penempatan dan tidak dapat dihapus.']);
        }

        $statusPegawai->delete();

        return redirect()->route('status-pegawai.daftar')->with('pesan', 'Status pegawai berhasil dihapus.');
    }

    /** @return array<string, mixed> */
    private function validasi(Request $permintaan): array
    {
        return $permintaan->validate([
            'nama' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string', 'max:5000'],
        ], [
            'required' => 'Nama status wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'max' => ':attribute maksimal :max karakter.',
        ], ['nama' => 'nama status']);
    }
}

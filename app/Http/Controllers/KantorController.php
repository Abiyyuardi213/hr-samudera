<?php

namespace App\Http\Controllers;

use App\Models\Kantor;
use App\Models\Kota;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KantorController extends Controller
{
    public function daftar(Request $permintaan): View
    {
        $pencarian = $permintaan->validate(['cari' => ['nullable', 'string', 'max:100']])['cari'] ?? '';
        $daftarKantor = Kantor::query()->with('kota')
            ->when($pencarian !== '', function ($kueri) use ($pencarian): void {
                $kueri->where(function ($kueri) use ($pencarian): void {
                    $kueri->where('nama_kantor', 'like', '%'.$pencarian.'%')
                        ->orWhere('kode_kantor', 'like', '%'.$pencarian.'%')
                        ->orWhereHas('kota', fn ($kueri) => $kueri->where('nama', 'like', '%'.$pencarian.'%'));
                });
            })->orderBy('nama_kantor')->paginate(15)->withQueryString();

        $daftarKota = Kota::query()->orderBy('nama_provinsi')->orderBy('nama')->get();

        return view('kantor.daftar', compact('daftarKantor', 'pencarian', 'daftarKota'));
    }

    public function tambah(): RedirectResponse
    {
        return redirect()->route('kantor.daftar', ['tambah' => 1]);
    }

    public function simpan(Request $permintaan): RedirectResponse
    {
        $data = $this->validasi($permintaan);
        retry(3, fn () => Kantor::query()->create($data), 0, fn (\Exception $kesalahan): bool => $kesalahan instanceof UniqueConstraintViolationException);

        return redirect()->route('kantor.daftar')->with('pesan', 'Kantor berhasil ditambahkan.');
    }

    public function tampilkan(Kantor $kantor): View
    {
        $kantor->load('kota');

        return view('kantor.tampilkan', compact('kantor'));
    }

    public function ubah(Kantor $kantor): View
    {
        $daftarKota = Kota::query()->orderBy('nama_provinsi')->orderBy('nama')->get();

        return view('kantor.ubah', compact('kantor', 'daftarKota'));
    }

    public function perbarui(Request $permintaan, Kantor $kantor): RedirectResponse
    {
        $kantor->update($this->validasi($permintaan));

        return redirect()->route('kantor.daftar')->with('pesan', 'Kantor berhasil diperbarui.');
    }

    public function hapus(Kantor $kantor): RedirectResponse
    {
        if ($kantor->pegawai()->exists() || $kantor->riwayatPenempatan()->exists()) {
            return back()->withErrors(['pegawai' => 'Kantor masih digunakan oleh pegawai atau riwayat penempatan dan tidak dapat dihapus.']);
        }

        $kantor->delete();

        return redirect()->route('kantor.daftar')->with('pesan', 'Kantor berhasil dihapus.');
    }

    /** @return array<string, mixed> */
    private function validasi(Request $permintaan): array
    {
        return $permintaan->validate([
            'nama_kantor' => ['required', 'string', 'max:255'],
            'kota_id' => ['required', 'integer', 'exists:kota,id'],
            'alamat_lengkap' => ['required', 'string', 'max:5000'],
            'nomor_telepon' => ['required', 'string', 'max:30', 'regex:/^[0-9+()\s.\-]+$/', 'regex:/\d/'],
            'email' => ['required', 'email', 'max:255'],
        ], [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'max' => ':attribute maksimal :max karakter.',
            'integer' => 'Kota tidak valid.',
            'exists' => 'Pilih kota dari data hasil sinkron.',
            'regex' => 'Nomor telepon tidak valid.',
            'email' => 'Format email tidak valid.',
        ], ['nama_kantor' => 'nama kantor', 'kota_id' => 'kota', 'alamat_lengkap' => 'alamat lengkap', 'nomor_telepon' => 'nomor telepon']);
    }
}

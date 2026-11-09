<?php

namespace App\Http\Controllers;

use App\Models\ShiftPegawai;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShiftPegawaiController extends Controller
{
    public function daftar(Request $permintaan): View
    {
        $data = $permintaan->validate(['cari' => ['nullable', 'string', 'max:100'], 'ubah' => ['nullable', 'uuid']]);
        $pencarian = $data['cari'] ?? '';
        $daftarShift = ShiftPegawai::query()->when($pencarian !== '', fn ($kueri) => $kueri->where('nama_shift', 'like', '%'.$pencarian.'%'))
            ->orderBy('jam_masuk')->orderBy('nama_shift')->paginate(15)->appends(['cari' => $pencarian]);
        $shift = ! empty($data['ubah']) ? ShiftPegawai::query()->findOrFail($data['ubah']) : null;

        return view('shift-pegawai.daftar', compact('daftarShift', 'pencarian', 'shift'));
    }

    public function tambah(): RedirectResponse
    {
        return redirect()->route('shift-pegawai.daftar', ['tambah' => 1]);
    }

    public function simpan(Request $permintaan): RedirectResponse
    {
        ShiftPegawai::query()->create($this->validasi($permintaan));

        return redirect()->route('shift-pegawai.daftar')->with('pesan', 'Shift berhasil ditambahkan.');
    }

    public function ubah(ShiftPegawai $shift): RedirectResponse
    {
        return redirect()->route('shift-pegawai.daftar', ['ubah' => $shift->id]);
    }

    public function perbarui(Request $permintaan, ShiftPegawai $shift): RedirectResponse
    {
        $shift->update($this->validasi($permintaan));

        return redirect()->route('shift-pegawai.daftar')->with('pesan', 'Shift berhasil diperbarui.');
    }

    public function hapus(ShiftPegawai $shift): RedirectResponse
    {
        if ($shift->pegawai()->exists() || $shift->riwayatPenempatan()->exists()) {
            return back()->withErrors(['pegawai' => 'Shift masih digunakan oleh pegawai atau riwayat penempatan dan tidak dapat dihapus.']);
        }

        $shift->delete();

        return redirect()->route('shift-pegawai.daftar')->with('pesan', 'Shift berhasil dihapus.');
    }

    /** @return array{nama_shift: string, jam_masuk: string, jam_pulang: string, wajib_qr: bool|int|string} */
    private function validasi(Request $permintaan): array
    {
        return $permintaan->validate([
            'nama_shift' => ['required', 'string', 'max:255'],
            'jam_masuk' => ['required', 'date_format:H:i'],
            'jam_pulang' => ['required', 'date_format:H:i', 'different:jam_masuk'],
            'wajib_qr' => ['required', 'boolean'],
        ], [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'max' => ':attribute maksimal :max karakter.',
            'date_format' => ':attribute harus menggunakan format HH:MM.',
            'different' => 'Jam pulang tidak boleh sama dengan jam masuk.',
            'boolean' => 'Pilih wajib QR atau tidak wajib.',
        ], ['nama_shift' => 'nama shift', 'jam_masuk' => 'jam masuk', 'jam_pulang' => 'jam pulang', 'wajib_qr' => 'ketentuan QR']);
    }
}

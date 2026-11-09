<?php

namespace App\Http\Controllers;

use App\Models\UnitOrganisasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UnitOrganisasiController extends Controller
{
    public function daftar(Request $permintaan): View
    {
        $data = $permintaan->validate(['cari' => ['nullable', 'string', 'max:100'], 'ubah' => ['nullable', 'uuid']]);
        $pencarian = $data['cari'] ?? '';
        $daftarUnit = UnitOrganisasi::query()->with('induk')->withCount('anak')
            ->when($pencarian !== '', function ($kueri) use ($pencarian): void {
                $kueri->where(function ($kueri) use ($pencarian): void {
                    $kueri->where('nama', 'like', '%'.$pencarian.'%')
                        ->orWhereHas('induk', fn ($kueri) => $kueri->where('nama', 'like', '%'.$pencarian.'%'));
                });
            })->orderBy('nama')->paginate(15)->appends(['cari' => $pencarian]);
        $unit = ! empty($data['ubah']) ? UnitOrganisasi::query()->findOrFail($data['ubah']) : null;
        $semuaUnit = UnitOrganisasi::query()->orderBy('nama')->get();
        $idTerlarang = $unit ? [$unit->id] : [];
        do {
            $jumlahSebelumnya = count($idTerlarang);
            foreach ($semuaUnit as $calonUnit) {
                if (in_array($calonUnit->induk_id, $idTerlarang, true) && ! in_array($calonUnit->id, $idTerlarang, true)) {
                    $idTerlarang[] = $calonUnit->id;
                }
            }
        } while (count($idTerlarang) > $jumlahSebelumnya);
        $daftarInduk = $semuaUnit->whereNotIn('id', $idTerlarang);
        $jenisUnit = UnitOrganisasi::JENIS_UNIT;

        return view('unit-organisasi.daftar', compact('daftarUnit', 'pencarian', 'unit', 'daftarInduk', 'jenisUnit'));
    }

    public function tambah(): RedirectResponse
    {
        return redirect()->route('unit-organisasi.daftar', ['tambah' => 1]);
    }

    public function simpan(Request $permintaan): RedirectResponse
    {
        $data = $this->validasi($permintaan);
        DB::transaction(function () use ($data): void {
            UnitOrganisasi::query()->orderBy('id')->lockForUpdate()->get();
            $this->periksaInduk($data['induk_id'] ?? null);
            UnitOrganisasi::query()->create($data);
        });

        return redirect()->route('unit-organisasi.daftar')->with('pesan', 'Unit organisasi berhasil ditambahkan.');
    }

    public function tampilkan(UnitOrganisasi $unit): View
    {
        $unit->load('induk');
        $daftarAnak = $unit->anak()->orderBy('nama')->paginate(15);
        $jenisUnit = UnitOrganisasi::JENIS_UNIT;

        return view('unit-organisasi.tampilkan', compact('unit', 'daftarAnak', 'jenisUnit'));
    }

    public function ubah(UnitOrganisasi $unit): RedirectResponse
    {
        return redirect()->route('unit-organisasi.daftar', ['ubah' => $unit->id]);
    }

    public function perbarui(Request $permintaan, UnitOrganisasi $unit): RedirectResponse
    {
        $data = $this->validasi($permintaan);
        DB::transaction(function () use ($data, $unit): void {
            UnitOrganisasi::query()->orderBy('id')->lockForUpdate()->get();
            $this->periksaInduk($data['induk_id'] ?? null, $unit->id);
            $unit->update($data);
        });

        return redirect()->route('unit-organisasi.daftar')->with('pesan', 'Unit organisasi berhasil diperbarui.');
    }

    public function hapus(UnitOrganisasi $unit): RedirectResponse
    {
        if ($unit->pegawai()->exists() || $unit->riwayatPenempatan()->exists()) {
            return back()->withErrors(['pegawai' => 'Unit organisasi masih digunakan oleh pegawai atau riwayat penempatan dan tidak dapat dihapus.']);
        }

        DB::transaction(function () use ($unit): void {
            UnitOrganisasi::query()->orderBy('id')->lockForUpdate()->get();
            if ($unit->jabatan()->exists()) {
                throw ValidationException::withMessages(['unit' => 'Unit masih digunakan oleh jabatan dan tidak dapat dihapus.']);
            }
            if ($unit->anak()->exists()) {
                throw ValidationException::withMessages(['unit' => 'Unit masih memiliki unit anak. Pindahkan atau hapus unit anak terlebih dahulu.']);
            }
            $unit->delete();
        });

        return redirect()->route('unit-organisasi.daftar')->with('pesan', 'Unit organisasi berhasil dihapus.');
    }

    private function periksaInduk(?string $indukId, ?string $idUnit = null): void
    {
        $sudahDiperiksa = [];
        while ($indukId !== null) {
            if ($indukId === $idUnit || in_array($indukId, $sudahDiperiksa, true)) {
                throw ValidationException::withMessages(['induk_id' => 'Induk unit tidak boleh diri sendiri atau unit turunan karena membentuk hubungan melingkar.']);
            }
            $sudahDiperiksa[] = $indukId;
            $induk = UnitOrganisasi::query()->find($indukId);
            if (! $induk) {
                throw ValidationException::withMessages(['induk_id' => 'Induk unit tidak ditemukan.']);
            }
            $indukId = $induk->induk_id;
        }
    }

    /** @return array<string, mixed> */
    private function validasi(Request $permintaan): array
    {
        return $permintaan->validate([
            'nama' => ['required', 'string', 'max:255'],
            'jenis_unit' => ['required', Rule::in(array_keys(UnitOrganisasi::JENIS_UNIT))],
            'induk_id' => ['nullable', 'uuid', 'exists:unit_organisasi,id'],
            'deskripsi' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', 'boolean'],
        ], [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'max' => ':attribute maksimal :max karakter.',
            'in' => 'Jenis unit tidak valid.',
            'uuid' => 'Induk unit tidak valid.',
            'exists' => 'Induk unit tidak ditemukan.',
            'boolean' => 'Status tidak valid.',
        ], ['nama' => 'nama unit', 'jenis_unit' => 'jenis unit', 'induk_id' => 'induk unit']);
    }
}

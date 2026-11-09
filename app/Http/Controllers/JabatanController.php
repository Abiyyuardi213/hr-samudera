<?php

namespace App\Http\Controllers;

use App\Models\Jabatan;
use App\Models\UnitOrganisasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class JabatanController extends Controller
{
    public function daftar(Request $permintaan): View
    {
        $data = $permintaan->validate(['cari' => ['nullable', 'string', 'max:100'], 'ubah' => ['nullable', 'uuid']]);
        $pencarian = $data['cari'] ?? '';
        $daftarJabatan = Jabatan::query()->with(['atasan', 'unitOrganisasi'])->withCount('bawahan')
            ->when($pencarian !== '', function ($kueri) use ($pencarian): void {
                $kueri->where(function ($kueri) use ($pencarian): void {
                    $kueri->where('nama', 'like', '%'.$pencarian.'%')
                        ->orWhereHas('atasan', fn ($kueri) => $kueri->where('nama', 'like', '%'.$pencarian.'%'))
                        ->orWhereHas('unitOrganisasi', fn ($kueri) => $kueri->where('nama', 'like', '%'.$pencarian.'%'));
                });
            })->orderBy('nama')->paginate(15)->appends(['cari' => $pencarian]);
        $jabatan = ! empty($data['ubah']) ? Jabatan::query()->findOrFail($data['ubah']) : null;
        $semuaJabatan = Jabatan::query()->orderBy('nama')->get();
        $idTerlarang = $jabatan ? [$jabatan->id] : [];
        do {
            $jumlahSebelumnya = count($idTerlarang);
            foreach ($semuaJabatan as $calonJabatan) {
                if (in_array($calonJabatan->atasan_id, $idTerlarang, true) && ! in_array($calonJabatan->id, $idTerlarang, true)) {
                    $idTerlarang[] = $calonJabatan->id;
                }
            }
        } while (count($idTerlarang) > $jumlahSebelumnya);
        $daftarAtasan = $semuaJabatan->whereNotIn('id', $idTerlarang);
        $daftarUnit = UnitOrganisasi::query()->orderBy('nama')->get();

        return view('jabatan.daftar', compact('daftarJabatan', 'pencarian', 'jabatan', 'daftarAtasan', 'daftarUnit'));
    }

    public function tambah(): RedirectResponse
    {
        return redirect()->route('jabatan.daftar', ['tambah' => 1]);
    }

    public function simpan(Request $permintaan): RedirectResponse
    {
        $data = $this->validasi($permintaan);
        DB::transaction(function () use ($data): void {
            Jabatan::query()->orderBy('id')->lockForUpdate()->get();
            $this->periksaAtasan($data['atasan_id'] ?? null);
            Jabatan::query()->create($data);
        });

        return redirect()->route('jabatan.daftar')->with('pesan', 'Jabatan berhasil ditambahkan.');
    }

    public function tampilkan(Jabatan $jabatan): View
    {
        $jabatan->load(['atasan', 'unitOrganisasi']);
        $daftarBawahan = $jabatan->bawahan()->orderBy('nama')->paginate(15);

        return view('jabatan.tampilkan', compact('jabatan', 'daftarBawahan'));
    }

    public function ubah(Jabatan $jabatan): RedirectResponse
    {
        return redirect()->route('jabatan.daftar', ['ubah' => $jabatan->id]);
    }

    public function perbarui(Request $permintaan, Jabatan $jabatan): RedirectResponse
    {
        $data = $this->validasi($permintaan);
        DB::transaction(function () use ($data, $jabatan): void {
            Jabatan::query()->orderBy('id')->lockForUpdate()->get();
            $this->periksaAtasan($data['atasan_id'] ?? null, $jabatan->id);
            $jabatan->update($data);
        });

        return redirect()->route('jabatan.daftar')->with('pesan', 'Jabatan berhasil diperbarui.');
    }

    public function hapus(Jabatan $jabatan): RedirectResponse
    {
        if ($jabatan->pegawai()->exists() || $jabatan->riwayatPenempatan()->exists()) {
            return back()->withErrors(['pegawai' => 'Jabatan masih digunakan oleh pegawai atau riwayat penempatan dan tidak dapat dihapus.']);
        }

        DB::transaction(function () use ($jabatan): void {
            Jabatan::query()->orderBy('id')->lockForUpdate()->get();
            if ($jabatan->bawahan()->exists()) {
                throw ValidationException::withMessages(['jabatan' => 'Jabatan masih memiliki bawahan. Pindahkan atau hapus jabatan bawahan terlebih dahulu.']);
            }
            $jabatan->delete();
        });

        return redirect()->route('jabatan.daftar')->with('pesan', 'Jabatan berhasil dihapus.');
    }

    private function periksaAtasan(?string $atasanId, ?string $idJabatan = null): void
    {
        $sudahDiperiksa = [];
        while ($atasanId !== null) {
            if ($atasanId === $idJabatan || in_array($atasanId, $sudahDiperiksa, true)) {
                throw ValidationException::withMessages(['atasan_id' => 'Atasan jabatan tidak boleh diri sendiri atau jabatan bawahan karena membentuk hubungan melingkar.']);
            }
            $sudahDiperiksa[] = $atasanId;
            $atasan = Jabatan::query()->find($atasanId);
            if (! $atasan) {
                throw ValidationException::withMessages(['atasan_id' => 'Atasan jabatan tidak ditemukan.']);
            }
            $atasanId = $atasan->atasan_id;
        }
    }

    /** @return array<string, mixed> */
    private function validasi(Request $permintaan): array
    {
        return $permintaan->validate([
            'nama' => ['required', 'string', 'max:255'],
            'unit_organisasi_id' => ['required', 'uuid', 'exists:unit_organisasi,id'],
            'atasan_id' => ['nullable', 'uuid', 'exists:jabatan,id'],
            'deskripsi' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', 'boolean'],
        ], [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'max' => ':attribute maksimal :max karakter.',
            'uuid' => ':attribute tidak valid.',
            'exists' => ':attribute tidak ditemukan.',
            'boolean' => 'Status tidak valid.',
        ], ['nama' => 'nama jabatan', 'unit_organisasi_id' => 'unit organisasi', 'atasan_id' => 'atasan jabatan']);
    }
}

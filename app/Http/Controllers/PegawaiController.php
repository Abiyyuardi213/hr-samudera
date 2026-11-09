<?php

namespace App\Http\Controllers;

use App\Models\Jabatan;
use App\Models\Kantor;
use App\Models\Pegawai;
use App\Models\PenempatanPegawai;
use App\Models\ShiftPegawai;
use App\Models\StatusPegawai;
use App\Models\UnitOrganisasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PegawaiController extends Controller
{
    public function daftar(Request $permintaan): View
    {
        $data = $permintaan->validate(['cari' => ['nullable', 'string', 'max:100'], 'ubah' => ['nullable', 'uuid']]);
        $pencarian = $data['cari'] ?? '';
        $daftarPegawai = Pegawai::query()->with(['jabatan', 'divisi', 'kantor', 'statusPegawai', 'shiftPegawai'])
            ->when($pencarian !== '', function ($kueri) use ($pencarian): void {
                $kueri->where(function ($kueri) use ($pencarian): void {
                    $kueri->where('nama_lengkap', 'like', '%'.$pencarian.'%')->orWhere('nip', 'like', '%'.$pencarian.'%')
                        ->orWhereHas('jabatan', fn ($kueri) => $kueri->where('nama', 'like', '%'.$pencarian.'%'))
                        ->orWhereHas('kantor', fn ($kueri) => $kueri->where('nama_kantor', 'like', '%'.$pencarian.'%'));
                });
            })->orderBy('nama_lengkap')->paginate(15)->appends(['cari' => $pencarian]);
        $pegawai = ! empty($data['ubah']) ? Pegawai::query()->findOrFail($data['ubah']) : null;
        $daftarJabatan = Jabatan::query()->with('unitOrganisasi')->orderBy('nama')->get();
        $daftarUnit = UnitOrganisasi::query()->get()->keyBy('id');
        $petaDivisi = $daftarJabatan->mapWithKeys(fn (Jabatan $jabatan): array => [$jabatan->id => $this->temukanDivisi($jabatan->unit_organisasi_id, $daftarUnit)]);
        $daftarKantor = Kantor::query()->orderBy('nama_kantor')->get();
        $daftarStatus = StatusPegawai::query()->orderBy('nama')->get();
        $daftarShift = ShiftPegawai::query()->orderBy('nama_shift')->get();

        return view('pegawai.daftar', compact('daftarPegawai', 'pencarian', 'pegawai', 'daftarJabatan', 'petaDivisi', 'daftarKantor', 'daftarStatus', 'daftarShift'));
    }

    public function tambah(): RedirectResponse
    {
        return redirect()->route('pegawai.daftar', ['tambah' => 1]);
    }

    public function simpan(Request $permintaan): RedirectResponse
    {
        $data = $this->validasi($permintaan);
        DB::transaction(function () use ($data, $permintaan): void {
            $pegawai = Pegawai::query()->create($data);
            PenempatanPegawai::buatAwal($pegawai, $permintaan->user()->id);
        });

        return redirect()->route('pegawai.daftar')->with('pesan', 'Pegawai berhasil ditambahkan.');
    }

    public function tampilkan(Pegawai $pegawai): View
    {
        $pegawai->load(['jabatan', 'divisi', 'kantor', 'statusPegawai', 'shiftPegawai']);

        return view('pegawai.tampilkan', compact('pegawai'));
    }

    public function ubah(Pegawai $pegawai): RedirectResponse
    {
        return redirect()->route('pegawai.daftar', ['ubah' => $pegawai->id]);
    }

    public function perbarui(Request $permintaan, Pegawai $pegawai): RedirectResponse
    {
        $data = $this->validasi($permintaan, $pegawai);
        DB::transaction(function () use ($pegawai, $data): void {
            $pegawai = Pegawai::query()->lockForUpdate()->findOrFail($pegawai->id);
            foreach (['jabatan_id', 'kantor_id', 'status_pegawai_id', 'shift_pegawai_id'] as $kolom) {
                if ((string) $data[$kolom] !== (string) $pegawai->{$kolom}) {
                    throw ValidationException::withMessages([$kolom => 'Gunakan modul Penempatan & Riwayat untuk mengubah penempatan pegawai.']);
                }
            }
            if ($data['tanggal_masuk'] !== $pegawai->tanggal_masuk->toDateString()) {
                throw ValidationException::withMessages(['tanggal_masuk' => 'Tanggal masuk tidak dapat diubah karena menjadi dasar riwayat penempatan.']);
            }
            $data['divisi_id'] = $pegawai->divisi_id;
            if ($pegawai->ketidakhadiran()->where('jenis', 'cuti')->exists() && (int) $data['sisa_cuti'] !== $pegawai->sisa_cuti) {
                throw ValidationException::withMessages(['sisa_cuti' => 'Saldo cuti sudah dikelola melalui pengajuan. Saldo tidak dapat diubah langsung dari profil pegawai.']);
            }
            PenempatanPegawai::buatAwal($pegawai);
            $pegawai->update($data);
        });

        return redirect()->route('pegawai.daftar')->with('pesan', 'Pegawai berhasil diperbarui.');
    }

    public function hapus(Pegawai $pegawai): RedirectResponse
    {
        if ($pegawai->riwayatPenempatan()->exists() || $pegawai->jadwalKerja()->exists() || $pegawai->ketidakhadiran()->exists()) {
            return back()->withErrors(['pegawai' => 'Pegawai memiliki riwayat penempatan dan tidak dapat dihapus.']);
        }
        $pegawai->delete();

        return redirect()->route('pegawai.daftar')->with('pesan', 'Pegawai berhasil dihapus.');
    }

    /** @param Collection<string, UnitOrganisasi> $daftarUnit */
    private function temukanDivisi(string $idUnit, Collection $daftarUnit): ?UnitOrganisasi
    {
        $sudahDiperiksa = [];
        while (! in_array($idUnit, $sudahDiperiksa, true)) {
            $sudahDiperiksa[] = $idUnit;
            $unit = $daftarUnit->get($idUnit);
            if (! $unit) {
                return null;
            }
            if ($unit->jenis_unit === 'divisi') {
                return $unit;
            }
            if (! $unit->induk_id) {
                return null;
            }
            $idUnit = $unit->induk_id;
        }

        return null;
    }

    /** @return array<string, mixed> */
    private function validasi(Request $permintaan, ?Pegawai $pegawai = null): array
    {
        $data = $permintaan->validate([
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'nip' => ['required', 'string', 'max:50', Rule::unique('pegawai', 'nip')->ignore($pegawai)],
            'jabatan_id' => ['required', 'uuid', 'exists:jabatan,id'],
            'kantor_id' => ['required', 'uuid', 'exists:kantor,id'],
            'status_pegawai_id' => ['required', 'uuid', 'exists:status_pegawai,id'],
            'nik' => ['required', 'string', 'regex:/^[0-9]{16}$/', Rule::unique('pegawai', 'nik')->ignore($pegawai)],
            'tempat_lahir' => ['required', 'string', 'max:255'],
            'tanggal_lahir' => ['required', 'date_format:Y-m-d', 'before:today'],
            'jenis_kelamin' => ['required', Rule::in(['laki_laki', 'perempuan'])],
            'alamat_domisili' => ['required', 'string', 'max:5000'],
            'tanggal_masuk' => ['required', 'date_format:Y-m-d', 'after:tanggal_lahir'],
            'shift_pegawai_id' => ['required', 'uuid', 'exists:shift_pegawai,id'],
            'sisa_cuti' => ['required', 'integer', 'min:0', 'max:365'],
            'nomor_telepon' => ['required', 'string', 'max:30', 'regex:/^[0-9+()\s.\-]+$/', 'regex:/\d/'],
            'email' => ['required', 'email', 'max:255', Rule::unique('pegawai', 'email')->ignore($pegawai)],
        ], [
            'required' => ':attribute wajib diisi.', 'string' => ':attribute harus berupa teks.',
            'max' => ':attribute melebihi batas :max.', 'min' => ':attribute minimal :min.',
            'unique' => ':attribute sudah digunakan.', 'uuid' => ':attribute tidak valid.', 'exists' => ':attribute tidak ditemukan.',
            'nik.regex' => 'NIK harus terdiri dari 16 digit angka.', 'nomor_telepon.regex' => 'Nomor telepon tidak valid.',
            'date_format' => ':attribute harus berupa tanggal yang valid.', 'before' => 'Tanggal lahir harus sebelum hari ini.',
            'after' => 'Tanggal masuk harus setelah tanggal lahir.', 'in' => ':attribute tidak valid.',
            'integer' => ':attribute harus berupa bilangan bulat.', 'email' => 'Format email tidak valid.',
        ], ['nama_lengkap' => 'nama lengkap', 'nip' => 'NIP', 'nik' => 'NIK', 'jabatan_id' => 'jabatan', 'kantor_id' => 'kantor', 'status_pegawai_id' => 'status pegawai', 'shift_pegawai_id' => 'shift kerja', 'tanggal_lahir' => 'tanggal lahir', 'tanggal_masuk' => 'tanggal masuk', 'tempat_lahir' => 'tempat lahir', 'jenis_kelamin' => 'jenis kelamin', 'alamat_domisili' => 'alamat domisili', 'sisa_cuti' => 'sisa cuti', 'nomor_telepon' => 'nomor telepon']);
        $jabatan = Jabatan::query()->findOrFail($data['jabatan_id']);
        $data['divisi_id'] = $this->temukanDivisi($jabatan->unit_organisasi_id, UnitOrganisasi::query()->get()->keyBy('id'))?->id;

        return $data;
    }
}

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
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PenempatanPegawaiController extends Controller
{
    public function daftar(Request $permintaan): View
    {
        $data = $permintaan->validate([
            'cari' => ['nullable', 'string', 'max:100'], 'pegawai_id' => ['nullable', 'uuid', 'exists:pegawai,id'],
            'jenis' => ['nullable', Rule::in(array_keys(PenempatanPegawai::JENIS))],
        ]);
        $pencarian = $data['cari'] ?? '';
        $pegawaiDipilih = ! empty($data['pegawai_id']) ? Pegawai::query()->with(['jabatan', 'divisi', 'kantor', 'statusPegawai', 'shiftPegawai'])->findOrFail($data['pegawai_id']) : null;
        $jenisDipilih = $data['jenis'] ?? '';
        $daftarRiwayat = PenempatanPegawai::query()->with('pegawai')
            ->when($pegawaiDipilih, fn ($kueri) => $kueri->where('pegawai_id', $pegawaiDipilih->id))
            ->when($jenisDipilih !== '', fn ($kueri) => $kueri->where('jenis', $jenisDipilih))
            ->when($pencarian !== '', function ($kueri) use ($pencarian): void {
                $kueri->where(function ($kueri) use ($pencarian): void {
                    $kueri->whereHas('pegawai', fn ($kueri) => $kueri->where('nama_lengkap', 'like', '%'.$pencarian.'%')->orWhere('nip', 'like', '%'.$pencarian.'%'))
                        ->orWhere('nama_kantor', 'like', '%'.$pencarian.'%')->orWhere('nama_jabatan', 'like', '%'.$pencarian.'%')->orWhere('nomor_sk', 'like', '%'.$pencarian.'%');
                });
            })->orderByDesc('tanggal_mulai')->orderByDesc('created_at')->paginate(15)->appends(array_filter($data));
        $daftarPegawai = Pegawai::query()->with(['jabatan', 'kantor', 'riwayatPenempatan' => fn ($kueri) => $kueri->whereNull('tanggal_selesai')])->orderBy('nama_lengkap')->get();
        $daftarJabatan = Jabatan::query()->with('unitOrganisasi')->orderBy('nama')->get();
        $semuaUnit = UnitOrganisasi::query()->get()->keyBy('id');
        $petaDivisi = $daftarJabatan->mapWithKeys(fn (Jabatan $jabatan): array => [$jabatan->id => $this->divisiJabatan($jabatan->unit_organisasi_id, $semuaUnit)]);
        $daftarKantor = Kantor::query()->orderBy('nama_kantor')->get();
        $daftarStatus = StatusPegawai::query()->orderBy('nama')->get();
        $daftarShift = ShiftPegawai::query()->orderBy('nama_shift')->get();
        $jenisPenempatan = PenempatanPegawai::JENIS;

        return view('penempatan-pegawai.daftar', compact('daftarRiwayat', 'pencarian', 'pegawaiDipilih', 'jenisDipilih', 'daftarPegawai', 'daftarJabatan', 'petaDivisi', 'daftarKantor', 'daftarStatus', 'daftarShift', 'jenisPenempatan'));
    }

    public function simpan(Request $permintaan): RedirectResponse
    {
        $data = $permintaan->validate([
            'pegawai_id' => ['required', 'uuid', 'exists:pegawai,id'],
            'versi_penempatan' => ['nullable', 'uuid'],
            'jabatan_id' => ['required', 'uuid', 'exists:jabatan,id'],
            'kantor_id' => ['required', 'uuid', 'exists:kantor,id'],
            'status_pegawai_id' => ['required', 'uuid', 'exists:status_pegawai,id'],
            'shift_pegawai_id' => ['required', 'uuid', 'exists:shift_pegawai,id'],
            'jenis' => ['required', Rule::in(array_values(array_diff(array_keys(PenempatanPegawai::JENIS), ['awal'])))],
            'tanggal_mulai' => ['required', 'date_format:Y-m-d', 'before_or_equal:'.now('Asia/Jakarta')->toDateString()],
            'nomor_sk' => ['nullable', 'string', 'max:100'],
            'keterangan' => ['required', 'string', 'max:5000'],
        ], [
            'required' => ':attribute wajib diisi.', 'uuid' => ':attribute tidak valid.', 'exists' => ':attribute tidak ditemukan.',
            'in' => 'Jenis penempatan tidak valid.', 'date_format' => 'Tanggal berlaku tidak valid.',
            'before_or_equal' => 'Tanggal berlaku maksimal hari ini.', 'string' => ':attribute harus berupa teks.', 'max' => ':attribute maksimal :max karakter.',
        ], ['pegawai_id' => 'pegawai', 'jabatan_id' => 'jabatan', 'kantor_id' => 'kantor', 'status_pegawai_id' => 'status pegawai', 'shift_pegawai_id' => 'shift', 'jenis' => 'jenis penempatan', 'tanggal_mulai' => 'tanggal berlaku', 'nomor_sk' => 'nomor SK', 'keterangan' => 'catatan perubahan']);
        $jabatan = Jabatan::query()->findOrFail($data['jabatan_id']);
        $data['divisi_id'] = $this->divisiJabatan($jabatan->unit_organisasi_id, UnitOrganisasi::query()->get()->keyBy('id'))?->id;
        $riwayat = PenempatanPegawai::catat(Pegawai::query()->findOrFail($data['pegawai_id']), $data, $permintaan->user()->id);

        return redirect()->route('penempatan-pegawai.daftar', ['pegawai_id' => $riwayat->pegawai_id])->with('pesan', 'Penempatan berhasil dicatat. Data aktif pegawai dan riwayat sebelumnya sudah diperbarui.');
    }

    public function tampilkan(PenempatanPegawai $penempatan): View
    {
        $penempatan->load('pegawai');
        $sebelumnya = $penempatan->pegawai->riwayatPenempatan()->where('tanggal_mulai', '<', $penempatan->tanggal_mulai)->orderByDesc('tanggal_mulai')->first();
        $jenisPenempatan = PenempatanPegawai::JENIS;

        return view('penempatan-pegawai.tampilkan', compact('penempatan', 'sebelumnya', 'jenisPenempatan'));
    }

    /** @param Collection<string, UnitOrganisasi> $semuaUnit */
    private function divisiJabatan(string $idUnit, Collection $semuaUnit): ?UnitOrganisasi
    {
        $dikunjungi = [];
        while (! in_array($idUnit, $dikunjungi, true)) {
            $dikunjungi[] = $idUnit;
            $unit = $semuaUnit->get($idUnit);
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
}

<?php

namespace App\Http\Controllers;

use App\Models\JadwalKerja;
use App\Models\Ketidakhadiran;
use App\Models\Lembur;
use App\Models\Pegawai;
use App\Models\RiwayatKeputusan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LemburController extends Controller
{
    public function daftar(Request $permintaan): View
    {
        $data = $permintaan->validate(['tanggal' => ['nullable', 'date_format:Y-m-d'], 'pegawai_id' => ['nullable', 'uuid', 'exists:pegawai,id'], 'status' => ['nullable', 'in:menunggu,disetujui,ditolak,dibatalkan']]);
        $tanggal = $data['tanggal'] ?? now('Asia/Jakarta')->toDateString();
        $daftarLembur = Lembur::query()->with(['jadwalKerja.pegawai', 'riwayatKeputusan'])->whereHas('jadwalKerja', fn ($kueri) => $kueri->where('tanggal', $tanggal)->when($data['pegawai_id'] ?? null, fn ($kueri, $id) => $kueri->where('pegawai_id', $id)))->when($data['status'] ?? null, fn ($kueri, $nilai) => $kueri->where('status', $nilai))->latest()->paginate(20)->withQueryString();
        $daftarJadwal = JadwalKerja::query()->with('pegawai')->where('tanggal', $tanggal)->orderBy('pegawai_id')->get();
        $daftarPegawai = Pegawai::query()->orderBy('nama_lengkap')->get();

        return view('lembur.daftar', compact('tanggal', 'daftarLembur', 'daftarJadwal', 'daftarPegawai'));
    }

    public function simpan(Request $permintaan): RedirectResponse
    {
        $data = $permintaan->validate(['jadwal_kerja_id' => ['required', 'uuid', 'exists:jadwal_kerja,id'], 'mulai' => ['required', 'date_format:Y-m-d\TH:i'], 'selesai' => ['required', 'date_format:Y-m-d\TH:i', 'after:mulai'], 'alasan' => ['required', 'string', 'min:5', 'max:5000']]);
        $jadwal = JadwalKerja::query()->findOrFail($data['jadwal_kerja_id']);
        DB::transaction(function () use ($data, $jadwal, $permintaan): void {
            Pegawai::query()->lockForUpdate()->findOrFail($jadwal->pegawai_id);
            $jadwal = JadwalKerja::query()->lockForUpdate()->findOrFail($jadwal->id);
            $mulai = JadwalKerja::waktu($data['mulai']);
            $selesai = JadwalKerja::waktu($data['selesai']);
            $batasAwal = JadwalKerja::waktu($jadwal->tanggal->toDateString());
            $batasAkhir = $batasAwal->copy()->addDays(2);
            if ($mulai->lt($batasAwal) || $selesai->gt($batasAkhir) || $mulai->diffInMinutes($selesai) > 720) {
                throw ValidationException::withMessages(['mulai' => 'Lembur harus dalam tanggal jadwal sampai akhir hari berikutnya, maksimal 12 jam.']);
            }
            $nilaiMulai = $mulai->format('Y-m-d H:i:s');
            $nilaiSelesai = $selesai->format('Y-m-d H:i:s');
            if (JadwalKerja::query()->where('pegawai_id', $jadwal->pegawai_id)->where('mulai', '<', $nilaiSelesai)->where('selesai', '>', $nilaiMulai)->exists()) {
                throw ValidationException::withMessages(['mulai' => 'Waktu lembur tidak boleh bertumpang tindih dengan jam kerja terjadwal.']);
            }
            if (Lembur::query()->whereHas('jadwalKerja', fn ($kueri) => $kueri->where('pegawai_id', $jadwal->pegawai_id))->whereIn('status', ['menunggu', 'disetujui'])->where('mulai', '<', $nilaiSelesai)->where('selesai', '>', $nilaiMulai)->exists()) {
                throw ValidationException::withMessages(['mulai' => 'Waktu lembur bertumpang tindih dengan pengajuan lembur aktif.']);
            }
            $tanggalTerakhir = $selesai->copy()->subSecond()->toDateString();
            if (Ketidakhadiran::query()->where('pegawai_id', $jadwal->pegawai_id)->whereIn('status', ['menunggu', 'disetujui'])->where('tanggal_mulai', '<=', $tanggalTerakhir)->where('tanggal_selesai', '>=', $mulai->toDateString())->exists() || Ketidakhadiran::aktifPada($jadwal->pegawai_id, $jadwal->tanggal->toDateString())->exists()) {
                throw ValidationException::withMessages(['mulai' => 'Tanggal lembur memiliki pengajuan cuti/izin/sakit aktif.']);
            }
            Lembur::query()->create(['jadwal_kerja_id' => $jadwal->id, 'mulai' => $nilaiMulai, 'selesai' => $nilaiSelesai, 'durasi' => (int) $mulai->diffInMinutes($selesai), 'alasan' => $data['alasan'], 'pencatat_id' => $permintaan->user()->id, 'nama_pencatat' => $permintaan->user()->name]);
        }, 3);

        return redirect()->route('lembur.daftar', ['tanggal' => $jadwal->tanggal->toDateString()])->with('pesan', 'Pengajuan lembur berhasil disimpan.');
    }

    public function keputusan(Request $permintaan, Lembur $lembur): RedirectResponse
    {
        $data = $permintaan->validate(['keputusan' => ['required', 'in:disetujui,ditolak,dibatalkan'], 'catatan_keputusan' => ['required', 'string', 'min:5', 'max:2000']]);
        DB::transaction(function () use ($lembur, $data, $permintaan): void {
            $jadwal = $lembur->jadwalKerja;
            Pegawai::query()->lockForUpdate()->findOrFail($jadwal->pegawai_id);
            $lembur = Lembur::query()->lockForUpdate()->findOrFail($lembur->id);
            if (($data['keputusan'] === 'dibatalkan' && ! in_array($lembur->status, ['menunggu', 'disetujui'], true)) || ($data['keputusan'] !== 'dibatalkan' && $lembur->status !== 'menunggu')) {
                throw ValidationException::withMessages(['status' => 'Status lembur telah berubah. Muat ulang halaman.']);
            }
            RiwayatKeputusan::query()->create(['lembur_id' => $lembur->id, 'status_lama' => $lembur->status, 'status_baru' => $data['keputusan'], 'pemutus_id' => $permintaan->user()->id, 'nama_pemutus' => $permintaan->user()->name, 'catatan' => $data['catatan_keputusan']]);
            $lembur->update(['status' => $data['keputusan'], 'catatan_keputusan' => $data['catatan_keputusan'], 'pemutus_id' => $permintaan->user()->id, 'nama_pemutus' => $permintaan->user()->name, 'diputus_pada' => now()]);
        }, 3);

        return back()->with('pesan', 'Keputusan lembur berhasil dicatat.');
    }
}

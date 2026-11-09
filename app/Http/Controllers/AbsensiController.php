<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\JadwalKerja;
use App\Models\Ketidakhadiran;
use App\Models\Pegawai;
use App\Models\RiwayatAbsensi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AbsensiController extends Controller
{
    public function daftar(Request $permintaan): View
    {
        $data = $permintaan->validate(['tanggal' => ['nullable', 'date_format:Y-m-d'], 'pegawai_id' => ['nullable', 'uuid', 'exists:pegawai,id'], 'catat' => ['nullable', 'uuid']]);
        $tanggal = $data['tanggal'] ?? now('Asia/Jakarta')->toDateString();
        $daftarJadwal = JadwalKerja::query()->with(['pegawai', 'absensi'])->where('tanggal', $tanggal)->when($data['pegawai_id'] ?? null, fn ($kueri, $id) => $kueri->where('pegawai_id', $id))->orderBy('pegawai_id')->paginate(25)->withQueryString();
        $daftarPengajuan = Ketidakhadiran::query()->where('status', 'disetujui')->where('tanggal_mulai', '<=', $tanggal)->where('tanggal_selesai', '>=', $tanggal)->get()->keyBy('pegawai_id');
        $jadwal = ! empty($data['catat']) ? JadwalKerja::query()->with(['pegawai', 'absensi.riwayat'])->findOrFail($data['catat']) : null;
        $daftarPegawai = Pegawai::query()->orderBy('nama_lengkap')->get();

        return view('absensi.daftar', compact('tanggal', 'daftarJadwal', 'daftarPengajuan', 'jadwal', 'daftarPegawai'));
    }

    public function simpan(Request $permintaan, JadwalKerja $jadwal): RedirectResponse
    {
        $data = $permintaan->validate(['masuk' => ['required', 'date_format:Y-m-d\TH:i'], 'pulang' => ['nullable', 'date_format:Y-m-d\TH:i', 'after:masuk'], 'keterangan' => ['required', 'string', 'min:5', 'max:2000'], 'versi' => ['required', 'integer', 'min:0']]);
        DB::transaction(function () use ($data, $jadwal, $permintaan): void {
            Pegawai::query()->lockForUpdate()->findOrFail($jadwal->pegawai_id);
            $jadwal = JadwalKerja::query()->lockForUpdate()->findOrFail($jadwal->id);
            if ($jadwal->libur || Ketidakhadiran::aktifPada($jadwal->pegawai_id, $jadwal->tanggal->toDateString())->exists()) {
                throw ValidationException::withMessages(['jadwal' => 'Absensi tidak dapat dicatat pada hari libur atau tanggal pengajuan cuti/izin/sakit aktif.']);
            }
            $absensi = $jadwal->absensi;
            $versi = (int) ($absensi?->versi ?? 0);
            if ((int) $data['versi'] !== $versi) {
                throw ValidationException::withMessages(['versi' => 'Absensi telah berubah. Muat ulang halaman sebelum menyimpan kembali.']);
            }
            $mulai = JadwalKerja::waktu($jadwal->getRawOriginal('mulai'));
            $selesai = JadwalKerja::waktu($jadwal->getRawOriginal('selesai'));
            $masuk = JadwalKerja::waktu($data['masuk']);
            $pulang = empty($data['pulang']) ? null : JadwalKerja::waktu($data['pulang']);
            if ($masuk->gt(now('Asia/Jakarta')) || $pulang?->gt(now('Asia/Jakarta')) || $masuk->lt($mulai->copy()->subHours(6)) || $masuk->gte($selesai) || $pulang?->gt($selesai->copy()->addHours(12))) {
                throw ValidationException::withMessages(['masuk' => 'Waktu harus sesuai jadwal: masuk paling awal 6 jam sebelum shift dan sebelum shift selesai; pulang maksimal 12 jam setelah shift. Waktu masa depan tidak diperbolehkan.']);
            }
            $lamaMasuk = $absensi?->getRawOriginal('masuk');
            $lamaPulang = $absensi?->getRawOriginal('pulang');
            if (Absensi::query()->whereHas('jadwalKerja', fn ($kueri) => $kueri->where('pegawai_id', $jadwal->pegawai_id))->where('jadwal_kerja_id', '!=', $jadwal->id)->where('masuk', '<', ($pulang ?? $masuk->copy()->addMinute())->format('Y-m-d H:i:s'))->where(function ($kueri) use ($masuk): void {
                $kueri->where('pulang', '>', $masuk->format('Y-m-d H:i:s'))->orWhereNull('pulang');
            })->exists()) {
                throw ValidationException::withMessages(['masuk' => 'Waktu bertumpang tindih dengan absensi lain atau ada absensi sebelumnya yang belum ditutup.']);
            }
            $absensi = Absensi::query()->updateOrCreate(['jadwal_kerja_id' => $jadwal->id], ['masuk' => $masuk->format('Y-m-d H:i:s'), 'pulang' => $pulang?->format('Y-m-d H:i:s'), 'terlambat' => max(0, (int) $mulai->diffInMinutes($masuk, false)), 'pulang_awal' => $pulang ? max(0, (int) $pulang->diffInMinutes($selesai, false)) : 0, 'menit_kerja' => $pulang ? (int) $masuk->diffInMinutes($pulang) : 0, 'keterangan' => $data['keterangan'], 'pencatat_id' => $permintaan->user()->id, 'versi' => $versi + 1]);
            RiwayatAbsensi::query()->create(['absensi_id' => $absensi->id, 'masuk_lama' => $lamaMasuk, 'pulang_lama' => $lamaPulang, 'masuk_baru' => $absensi->getRawOriginal('masuk'), 'pulang_baru' => $absensi->getRawOriginal('pulang'), 'alasan' => $data['keterangan'], 'nama_pencatat' => $permintaan->user()->name]);
        }, 3);

        return redirect()->route('absensi.daftar', ['tanggal' => $jadwal->tanggal->toDateString()])->with('pesan', 'Absensi manual dan jejak pencatatan berhasil disimpan.');
    }
}

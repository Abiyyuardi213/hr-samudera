<?php

namespace App\Http\Controllers;

use App\Models\JadwalKerja;
use App\Models\Kantor;
use App\Models\Ketidakhadiran;
use App\Models\Lembur;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanHrController extends Controller
{
    public function daftar(Request $permintaan): View|StreamedResponse
    {
        $penyaring = $permintaan->validate(['tanggal_mulai' => ['nullable', 'date_format:Y-m-d'], 'tanggal_selesai' => ['nullable', 'date_format:Y-m-d'], 'pegawai_id' => ['nullable', 'uuid', 'exists:pegawai,id'], 'kantor_id' => ['nullable', 'uuid', 'exists:kantor,id'], 'ekspor' => ['nullable', 'in:csv']]);
        $awal = $penyaring['tanggal_mulai'] ?? now('Asia/Jakarta')->startOfMonth()->toDateString();
        $akhir = $penyaring['tanggal_selesai'] ?? now('Asia/Jakarta')->endOfMonth()->toDateString();
        if ($akhir < $awal || JadwalKerja::waktu($awal)->diffInDays(JadwalKerja::waktu($akhir)) > 365) {
            throw ValidationException::withMessages(['tanggal_selesai' => 'Periode laporan harus berurutan dan maksimal 366 hari.']);
        }
        $daftarPegawai = Pegawai::query()->orderBy('nama_lengkap')->get();
        $daftarKantor = Kantor::query()->orderBy('nama_kantor')->get();
        $pegawaiTerpilih = Pegawai::query()->with(['kantor', 'jadwalKerja' => fn ($kueri) => $kueri->with('absensi')->whereBetween('tanggal', [$awal, $akhir]), 'ketidakhadiran' => fn ($kueri) => $kueri->where('status', 'disetujui')->where('tanggal_mulai', '<=', $akhir)->where('tanggal_selesai', '>=', $awal)])
            ->when($penyaring['pegawai_id'] ?? null, fn ($kueri, $id) => $kueri->whereKey($id))->when($penyaring['kantor_id'] ?? null, fn ($kueri, $id) => $kueri->where('kantor_id', $id))->orderBy('nama_lengkap')->get();
        $lembur = Lembur::query()->with('jadwalKerja')->where('status', 'disetujui')->whereHas('jadwalKerja', fn ($kueri) => $kueri->whereBetween('tanggal', [$awal, $akhir])->whereIn('pegawai_id', $pegawaiTerpilih->modelKeys()))->get()->groupBy(fn (Lembur $baris): string => $baris->jadwalKerja->pegawai_id);
        $rekap = $pegawaiTerpilih->map(function (Pegawai $pegawai) use ($lembur): array {
            $baris = ['pegawai' => $pegawai, 'hari_kerja' => 0, 'libur' => 0, 'hadir' => 0, 'belum_pulang' => 0, 'cuti' => 0, 'izin' => 0, 'sakit' => 0, 'tanpa_keterangan' => 0, 'belum_berakhir' => 0, 'terlambat' => 0, 'pulang_awal' => 0, 'menit_kerja' => 0, 'lembur' => $lembur->get($pegawai->id, collect())->sum('durasi')];
            foreach ($pegawai->jadwalKerja as $jadwal) {
                if ($jadwal->libur) {
                    $baris['libur']++;

                    continue;
                }
                $baris['hari_kerja']++;
                $tanggal = $jadwal->tanggal->toDateString();
                $pengajuan = $pegawai->ketidakhadiran->first(fn (Ketidakhadiran $pengajuan): bool => $pengajuan->tanggal_mulai->toDateString() <= $tanggal && $pengajuan->tanggal_selesai->toDateString() >= $tanggal);
                if ($pengajuan) {
                    $baris[$pengajuan->jenis]++;
                } elseif ($jadwal->absensi) {
                    $baris['hadir']++;
                    $baris['belum_pulang'] += $jadwal->absensi->pulang ? 0 : 1;
                    foreach (['terlambat', 'pulang_awal', 'menit_kerja'] as $kolom) {
                        $baris[$kolom] += $jadwal->absensi->{$kolom};
                    }
                } elseif (JadwalKerja::waktu($jadwal->getRawOriginal('selesai'))->lt(now('Asia/Jakarta'))) {
                    $baris['tanpa_keterangan']++;
                } else {
                    $baris['belum_berakhir']++;
                }
            }

            return $baris;
        });
        if (($penyaring['ekspor'] ?? null) === 'csv') {
            return response()->streamDownload(function () use ($rekap): void {
                $berkas = fopen('php://output', 'w');
                fwrite($berkas, "\xEF\xBB\xBF");
                fputcsv($berkas, ['NIP', 'Nama', 'Kantor saat ini', 'Hari kerja', 'Libur', 'Hadir', 'Belum pulang', 'Cuti', 'Izin', 'Sakit', 'Tanpa keterangan', 'Belum berakhir', 'Terlambat (menit)', 'Pulang awal (menit)', 'Kerja tercatat (menit)', 'Lembur disetujui (menit)', 'Saldo cuti saat ini'], ';', '"', '');
                foreach ($rekap as $baris) {
                    $pegawai = $baris['pegawai'];
                    $nilai = [$pegawai->nip, $pegawai->nama_lengkap, $pegawai->kantor->nama_kantor, ...array_values(collect($baris)->except('pegawai')->all()), $pegawai->sisa_cuti];
                    $nilai = array_map(fn ($isi) => is_string($isi) && preg_match('/^[=+@\-\t\r\n]/u', $isi) ? "'".$isi : $isi, $nilai);
                    fputcsv($berkas, $nilai, ';', '"', '');
                }
                fclose($berkas);
            }, 'laporan-hr-'.$awal.'-'.$akhir.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
        }

        return view('laporan-hr.daftar', compact('awal', 'akhir', 'rekap', 'daftarPegawai', 'daftarKantor'));
    }
}

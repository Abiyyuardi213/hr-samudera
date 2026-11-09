<?php

namespace App\Http\Controllers;

use App\Models\JadwalKerja;
use App\Models\Ketidakhadiran;
use App\Models\Lembur;
use App\Models\Pegawai;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class KetidakhadiranController extends Controller
{
    public function daftar(Request $permintaan): View
    {
        $data = $permintaan->validate(['pegawai_id' => ['nullable', 'uuid', 'exists:pegawai,id'], 'status' => ['nullable', 'in:menunggu,disetujui,ditolak,dibatalkan'], 'jenis' => ['nullable', 'in:cuti,izin,sakit']]);
        $daftarPengajuan = Ketidakhadiran::query()->with(['pegawai', 'riwayatKeputusan'])->when($data['pegawai_id'] ?? null, fn ($kueri, $id) => $kueri->where('pegawai_id', $id))->when($data['status'] ?? null, fn ($kueri, $nilai) => $kueri->where('status', $nilai))->when($data['jenis'] ?? null, fn ($kueri, $nilai) => $kueri->where('jenis', $nilai))->latest()->paginate(20)->withQueryString();
        $daftarPegawai = Pegawai::query()->orderBy('nama_lengkap')->get();

        return view('ketidakhadiran.daftar', compact('daftarPengajuan', 'daftarPegawai'));
    }

    public function simpan(Request $permintaan): RedirectResponse
    {
        $data = $permintaan->validate(['pegawai_id' => ['required', 'uuid', 'exists:pegawai,id'], 'jenis' => ['required', 'in:cuti,izin,sakit'], 'tanggal_mulai' => ['required', 'date_format:Y-m-d'], 'tanggal_selesai' => ['required', 'date_format:Y-m-d', 'after_or_equal:tanggal_mulai'], 'alasan' => ['required', 'string', 'min:5', 'max:5000'], 'lampiran' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120']]);
        $lampiran = null;
        try {
            DB::transaction(function () use ($data, $permintaan, &$lampiran): void {
                $pegawai = Pegawai::query()->lockForUpdate()->findOrFail($data['pegawai_id']);
                $awal = JadwalKerja::waktu($data['tanggal_mulai']);
                if ($awal->lt(JadwalKerja::waktu($pegawai->tanggal_masuk->toDateString())) || $awal->diffInDays(JadwalKerja::waktu($data['tanggal_selesai'])) > 365) {
                    throw ValidationException::withMessages(['tanggal_mulai' => 'Periode harus sejak tanggal masuk dan maksimal 366 hari.']);
                }
                if (Ketidakhadiran::query()->where('pegawai_id', $pegawai->id)->whereIn('status', ['menunggu', 'disetujui'])->where('tanggal_mulai', '<=', $data['tanggal_selesai'])->where('tanggal_selesai', '>=', $data['tanggal_mulai'])->exists()) {
                    throw ValidationException::withMessages(['tanggal_mulai' => 'Periode bertumpang tindih dengan pengajuan aktif.']);
                }
                $jadwal = JadwalKerja::query()->where('pegawai_id', $pegawai->id)->whereBetween('tanggal', [$data['tanggal_mulai'], $data['tanggal_selesai']]);
                $jumlahTanggal = (int) $awal->diffInDays(JadwalKerja::waktu($data['tanggal_selesai'])) + 1;
                if ((clone $jadwal)->count() !== $jumlahTanggal) {
                    throw ValidationException::withMessages(['tanggal_mulai' => 'Lengkapi jadwal untuk seluruh tanggal pengajuan, termasuk jadwal Libur, agar jumlah hari cuti tidak keliru.']);
                }
                if (Lembur::query()->whereHas('jadwalKerja', fn ($kueri) => $kueri->where('pegawai_id', $pegawai->id))->whereIn('status', ['menunggu', 'disetujui'])->where('mulai', '<', JadwalKerja::waktu($data['tanggal_selesai'])->addDay()->format('Y-m-d H:i:s'))->where('selesai', '>', $awal->format('Y-m-d H:i:s'))->exists()) {
                    throw ValidationException::withMessages(['tanggal_mulai' => 'Periode bersinggungan dengan lembur aktif, termasuk lembur lintas tengah malam.']);
                }
                if ((clone $jadwal)->whereHas('absensi')->exists() || (clone $jadwal)->whereHas('lembur', fn ($kueri) => $kueri->whereIn('status', ['menunggu', 'disetujui']))->exists()) {
                    throw ValidationException::withMessages(['tanggal_mulai' => 'Periode telah memiliki absensi atau pengajuan lembur aktif.']);
                }
                $jumlah = (clone $jadwal)->where('libur', false)->count();
                if ($jumlah === 0) {
                    throw ValidationException::withMessages(['tanggal_mulai' => 'Buat jadwal kerja terlebih dahulu. Periode pengajuan harus memiliki hari kerja.']);
                }
                if ($data['jenis'] === 'cuti' && $jumlah > $pegawai->sisa_cuti) {
                    throw ValidationException::withMessages(['saldo' => 'Saldo cuti tidak mencukupi untuk jumlah hari kerja yang dipilih.']);
                }
                if ($permintaan->hasFile('lampiran')) {
                    $lampiran = $permintaan->file('lampiran')->store('lampiran-hr', 'local');
                    if (! $lampiran) {
                        throw ValidationException::withMessages(['lampiran' => 'Lampiran gagal disimpan. Silakan coba lagi.']);
                    }
                }
                Ketidakhadiran::query()->create([...collect($data)->except('lampiran')->all(), 'jumlah_hari' => $jumlah, 'lampiran' => $lampiran, 'pencatat_id' => $permintaan->user()->id, 'nama_pencatat' => $permintaan->user()->name]);
            });
        } catch (\Throwable $kesalahan) {
            if ($lampiran) {
                Storage::disk('local')->delete($lampiran);
            }
            throw $kesalahan;
        }

        return redirect()->route('ketidakhadiran.daftar')->with('pesan', 'Pengajuan disimpan dan menunggu persetujuan. Jumlah hari dihitung dari jadwal kerja, tidak termasuk libur.');
    }

    public function keputusan(Request $permintaan, Ketidakhadiran $pengajuan): RedirectResponse
    {
        $data = $permintaan->validate(['keputusan' => ['required', 'in:disetujui,ditolak,dibatalkan'], 'catatan_keputusan' => ['required', 'string', 'min:5', 'max:2000']]);
        $pengajuan->putuskan($data['keputusan'], $data['catatan_keputusan'], $permintaan->user());

        return back()->with('pesan', 'Keputusan pengajuan berhasil dicatat.');
    }

    public function lampiran(Ketidakhadiran $pengajuan): StreamedResponse
    {
        abort_unless($pengajuan->lampiran && Storage::disk('local')->exists($pengajuan->lampiran), 404);

        return Storage::disk('local')->download($pengajuan->lampiran);
    }
}

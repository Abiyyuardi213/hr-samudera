<?php

namespace App\Http\Controllers;

use App\Models\JadwalKerja;
use App\Models\Ketidakhadiran;
use App\Models\Lembur;
use App\Models\Pegawai;
use App\Models\ShiftPegawai;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class JadwalKerjaController extends Controller
{
    public function daftar(Request $permintaan): View
    {
        $penyaring = $permintaan->validate(['bulan' => ['nullable', 'date_format:Y-m'], 'pegawai_id' => ['nullable', 'uuid', 'exists:pegawai,id'], 'ubah' => ['nullable', 'uuid']]);
        $bulan = $penyaring['bulan'] ?? now('Asia/Jakarta')->format('Y-m');
        $awal = JadwalKerja::waktu($bulan.'-01');
        $daftarJadwal = JadwalKerja::query()->with(['pegawai', 'absensi'])->whereBetween('tanggal', [$awal->toDateString(), $awal->copy()->endOfMonth()->toDateString()])
            ->when($penyaring['pegawai_id'] ?? null, fn ($kueri, $id) => $kueri->where('pegawai_id', $id))->orderBy('tanggal')->orderBy('pegawai_id')->paginate(25)->withQueryString();
        $jadwal = ! empty($penyaring['ubah']) ? JadwalKerja::query()->findOrFail($penyaring['ubah']) : null;
        $daftarPegawai = Pegawai::query()->orderBy('nama_lengkap')->get();
        $daftarShift = ShiftPegawai::query()->orderBy('nama_shift')->get();

        return view('jadwal-kerja.daftar', compact('daftarJadwal', 'jadwal', 'daftarPegawai', 'daftarShift', 'bulan'));
    }

    public function simpan(Request $permintaan): RedirectResponse
    {
        $data = $this->validasi($permintaan);
        DB::transaction(function () use ($data): void {
            $pegawai = Pegawai::query()->with('kantor')->lockForUpdate()->findOrFail($data['pegawai_id']);
            $awal = JadwalKerja::waktu($data['tanggal_mulai']);
            $akhir = JadwalKerja::waktu($data['tanggal_selesai']);
            if ($awal->lt(JadwalKerja::waktu($pegawai->tanggal_masuk->toDateString())) || $awal->diffInDays($akhir) > 61) {
                throw ValidationException::withMessages(['tanggal_mulai' => 'Periode harus sejak tanggal masuk pegawai dan maksimal 62 hari.']);
            }
            $shift = empty($data['shift_pegawai_id']) ? null : ShiftPegawai::query()->findOrFail($data['shift_pegawai_id']);
            $jumlah = 0;
            for ($tanggal = $awal->copy(); $tanggal->lte($akhir); $tanggal->addDay()) {
                if (! in_array((string) $tanggal->dayOfWeekIso, $data['hari'], true)) {
                    continue;
                }
                $nilai = $tanggal->toDateString();
                if (JadwalKerja::query()->where('pegawai_id', $pegawai->id)->where('tanggal', $nilai)->exists() || Ketidakhadiran::aktifPada($pegawai->id, $nilai)->exists()) {
                    throw ValidationException::withMessages(['tanggal_mulai' => 'Periode berisi jadwal atau pengajuan aktif. Tidak ada jadwal baru disimpan; pilih tanggal kosong atau ubah jadwal satu per satu.']);
                }
                $atribut = $this->atribut($nilai, $shift, $data);
                $this->pastikanTidakBentrokLembur($pegawai->id, $atribut);
                if ($atribut['mulai'] && JadwalKerja::query()->where('pegawai_id', $pegawai->id)->where('mulai', '<', $atribut['selesai'])->where('selesai', '>', $atribut['mulai'])->exists()) {
                    throw ValidationException::withMessages(['tanggal_mulai' => 'Jam jadwal bertumpang tindih dengan jadwal lain.']);
                }
                JadwalKerja::query()->create(['pegawai_id' => $pegawai->id, 'tanggal' => $nilai, 'nama_kantor' => $pegawai->kantor->nama_kantor, ...$atribut]);
                $jumlah++;
            }
            if ($jumlah === 0) {
                throw ValidationException::withMessages(['hari' => 'Tidak ada hari terpilih dalam periode ini.']);
            }
        }, 3);

        return redirect()->route('jadwal-kerja.daftar', ['bulan' => substr($data['tanggal_mulai'], 0, 7), 'pegawai_id' => $data['pegawai_id']])->with('pesan', 'Jadwal berhasil dibuat.');
    }

    public function perbarui(Request $permintaan, JadwalKerja $jadwal): RedirectResponse
    {
        $data = $this->validasi($permintaan, true);
        DB::transaction(function () use ($jadwal, $data): void {
            Pegawai::query()->lockForUpdate()->findOrFail($jadwal->pegawai_id);
            $jadwal = JadwalKerja::query()->lockForUpdate()->findOrFail($jadwal->id);
            $jadwal->pastikanDapatDiubah();
            $shift = empty($data['shift_pegawai_id']) ? null : ShiftPegawai::query()->findOrFail($data['shift_pegawai_id']);
            $atribut = $this->atribut($jadwal->tanggal->toDateString(), $shift, $data);
            $this->pastikanTidakBentrokLembur($jadwal->pegawai_id, $atribut);
            if ($atribut['mulai'] && JadwalKerja::query()->where('pegawai_id', $jadwal->pegawai_id)->whereKeyNot($jadwal->id)->where('mulai', '<', $atribut['selesai'])->where('selesai', '>', $atribut['mulai'])->exists()) {
                throw ValidationException::withMessages(['jadwal' => 'Jam jadwal bertumpang tindih dengan jadwal lain.']);
            }
            $jadwal->update($atribut);
        }, 3);

        return redirect()->route('jadwal-kerja.daftar', ['bulan' => $jadwal->tanggal->format('Y-m'), 'pegawai_id' => $jadwal->pegawai_id])->with('pesan', 'Jadwal berhasil diperbarui.');
    }

    public function hapus(JadwalKerja $jadwal): RedirectResponse
    {
        DB::transaction(function () use ($jadwal): void {
            Pegawai::query()->lockForUpdate()->findOrFail($jadwal->pegawai_id);
            $jadwal = JadwalKerja::query()->lockForUpdate()->findOrFail($jadwal->id);
            $jadwal->pastikanDapatDiubah();
            if ($jadwal->lembur()->exists()) {
                throw ValidationException::withMessages(['jadwal' => 'Jadwal memiliki riwayat lembur dan tidak dapat dihapus.']);
            }
            $jadwal->delete();
        }, 3);

        return back()->with('pesan', 'Jadwal berhasil dihapus.');
    }

    /** @return array<string, mixed> */
    private function validasi(Request $permintaan, bool $ubah = false): array
    {
        return $permintaan->validate([
            ...($ubah ? [] : ['pegawai_id' => ['required', 'uuid', 'exists:pegawai,id'], 'tanggal_mulai' => ['required', 'date_format:Y-m-d'], 'tanggal_selesai' => ['required', 'date_format:Y-m-d', 'after_or_equal:tanggal_mulai'], 'hari' => ['required', 'array', 'min:1'], 'hari.*' => ['required', 'string', 'in:1,2,3,4,5,6,7', 'distinct']]),
            'libur' => ['required', 'boolean'], 'shift_pegawai_id' => ['nullable', 'required_if:libur,0', 'uuid', 'exists:shift_pegawai,id'], 'keterangan' => ['nullable', 'string', 'max:2000'],
        ]);
    }

    /** @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function atribut(string $tanggal, ?ShiftPegawai $shift, array $data): array
    {
        $libur = (bool) $data['libur'];
        $mulai = $libur ? null : JadwalKerja::waktu($tanggal.' '.$shift->jam_masuk);
        $selesai = $libur ? null : $mulai->copy()->addMinutes($shift->durasi);

        return ['libur' => $libur, 'nama_shift' => $libur ? null : $shift->nama_shift, 'mulai' => $mulai?->format('Y-m-d H:i:s'), 'selesai' => $selesai?->format('Y-m-d H:i:s'), 'wajib_qr' => ! $libur && $shift->wajib_qr, 'keterangan' => $data['keterangan'] ?? null];
    }

    /** @param array<string, mixed> $atribut */
    private function pastikanTidakBentrokLembur(string $pegawaiId, array $atribut): void
    {
        if ($atribut['mulai'] && Lembur::query()->whereHas('jadwalKerja', fn ($kueri) => $kueri->where('pegawai_id', $pegawaiId))->whereIn('status', ['menunggu', 'disetujui'])->where('mulai', '<', $atribut['selesai'])->where('selesai', '>', $atribut['mulai'])->exists()) {
            throw ValidationException::withMessages(['jadwal' => 'Jam kerja bertumpang tindih dengan lembur aktif.']);
        }
    }
}

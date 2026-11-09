<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\Jabatan;
use App\Models\JadwalKerja;
use App\Models\Kantor;
use App\Models\Ketidakhadiran;
use App\Models\Kota;
use App\Models\Lembur;
use App\Models\Pegawai;
use App\Models\Role;
use App\Models\ShiftPegawai;
use App\Models\StatusPegawai;
use App\Models\UnitOrganisasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OperasionalHrTest extends TestCase
{
    use RefreshDatabase;

    private Pegawai $pegawai;

    private ShiftPegawai $shift;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(Carbon::parse('2026-11-07 18:00:00', 'Asia/Jakarta'));
        $role = Role::query()->create(['nama' => 'admin', 'status' => true]);
        $this->actingAs(User::factory()->create(['role_id' => $role->id, 'status' => true]));
        $unit = UnitOrganisasi::query()->create(['nama' => 'SDM', 'jenis_unit' => 'divisi']);
        $jabatan = Jabatan::query()->create(['nama' => 'Staf HR', 'unit_organisasi_id' => $unit->id]);
        $kota = Kota::query()->create(['kode' => '32.73', 'nama' => 'Bandung', 'kode_provinsi' => '32', 'nama_provinsi' => 'Jawa Barat', 'sinkron_pada' => now()]);
        $kantor = Kantor::query()->create(['nama_kantor' => 'Bandung', 'kota_id' => $kota->id, 'alamat_lengkap' => 'Jalan Kantor', 'nomor_telepon' => '022123', 'email' => 'kantor@example.com']);
        $status = StatusPegawai::query()->create(['nama' => 'Tetap']);
        $this->shift = ShiftPegawai::query()->create(['nama_shift' => 'Pagi', 'jam_masuk' => '08:00', 'jam_pulang' => '16:00', 'wajib_qr' => true]);
        $this->pegawai = Pegawai::query()->create(['nama_lengkap' => 'Pegawai Uji', 'nip' => '00123', 'jabatan_id' => $jabatan->id, 'divisi_id' => $unit->id, 'kantor_id' => $kantor->id, 'status_pegawai_id' => $status->id, 'shift_pegawai_id' => $this->shift->id, 'nik' => '0123456789012345', 'tempat_lahir' => 'Bandung', 'tanggal_lahir' => '1990-01-01', 'tanggal_masuk' => '2025-01-01', 'jenis_kelamin' => 'laki_laki', 'alamat_domisili' => 'Jalan Rumah', 'sisa_cuti' => 12, 'nomor_telepon' => '0812345', 'email' => 'pegawai@example.com']);
    }

    private function jadwal(string $tanggal = '2026-11-02', bool $libur = false): JadwalKerja
    {
        $this->post(route('jadwal-kerja.simpan'), ['pegawai_id' => $this->pegawai->id, 'tanggal_mulai' => $tanggal, 'tanggal_selesai' => $tanggal, 'hari' => ['1', '2', '3', '4', '5', '6', '7'], 'libur' => $libur ? '1' : '0', 'shift_pegawai_id' => $this->shift->id])->assertSessionHasNoErrors();

        return JadwalKerja::query()->where('tanggal', $tanggal)->firstOrFail();
    }

    private function pengajuan(string $jenis = 'cuti', string $awal = '2026-11-02', ?string $akhir = null): Ketidakhadiran
    {
        $this->post(route('ketidakhadiran.simpan'), ['pegawai_id' => $this->pegawai->id, 'jenis' => $jenis, 'tanggal_mulai' => $awal, 'tanggal_selesai' => $akhir ?? $awal, 'alasan' => 'Keperluan keluarga'])->assertSessionHasNoErrors();

        return Ketidakhadiran::query()->where('tanggal_mulai', $awal)->where('status', 'menunggu')->firstOrFail();
    }

    private function putuskan(Ketidakhadiran $pengajuan, string $keputusan = 'disetujui'): void
    {
        $this->post(route('ketidakhadiran.keputusan', $pengajuan), ['keputusan' => $keputusan, 'catatan_keputusan' => 'Keputusan oleh HR'])->assertSessionHasNoErrors();
    }

    public function test_seluruh_halaman_dan_modal_dapat_dibuka(): void
    {
        foreach (['jadwal-kerja', 'absensi', 'cuti-izin-sakit', 'lembur', 'laporan-hr'] as $jalur) {
            $this->get('/administrator/'.$jalur)->assertOk()->assertSee('Pegawai Uji');
        }
        $jadwal = $this->jadwal();
        $this->get(route('jadwal-kerja.daftar', ['ubah' => $jadwal->id]))->assertOk()->assertSee('Ubah Jadwal');
        $this->get(route('absensi.daftar', ['catat' => $jadwal->id]))->assertOk()->assertSee('Catat / Koreksi Absensi');
    }

    public function test_jadwal_massal_shift_malam_snapshot_dan_rollback_duplikat(): void
    {
        $this->shift->update(['jam_masuk' => '22:00', 'jam_pulang' => '06:00']);
        $this->post(route('jadwal-kerja.simpan'), ['pegawai_id' => $this->pegawai->id, 'tanggal_mulai' => '2026-11-02', 'tanggal_selesai' => '2026-11-08', 'hari' => ['1', '2', '3', '4', '5'], 'libur' => '0', 'shift_pegawai_id' => $this->shift->id])->assertSessionHasNoErrors();
        $this->assertDatabaseCount('jadwal_kerja', 5);
        $jadwal = JadwalKerja::query()->where('tanggal', '2026-11-02')->firstOrFail();
        $this->assertSame('2026-11-03 06:00:00', $jadwal->getRawOriginal('selesai'));
        $this->shift->update(['nama_shift' => 'Baru', 'jam_pulang' => '07:00']);
        $this->assertSame('Pagi', $jadwal->fresh()->nama_shift);
        $this->post(route('jadwal-kerja.simpan'), ['pegawai_id' => $this->pegawai->id, 'tanggal_mulai' => '2026-11-01', 'tanggal_selesai' => '2026-11-03', 'hari' => ['1', '2', '7'], 'libur' => '1'])->assertSessionHasErrors('tanggal_mulai');
        $this->assertDatabaseCount('jadwal_kerja', 5);
        $this->get(route('jadwal-kerja.daftar', ['bulan' => '2026-11']))->assertOk()->assertSee('Pagi');
    }

    public function test_validasi_jadwal_dan_perubahan_hari_libur(): void
    {
        $dasar = ['pegawai_id' => $this->pegawai->id, 'tanggal_mulai' => '2024-01-01', 'tanggal_selesai' => '2024-01-01', 'hari' => ['1'], 'libur' => '1'];
        $this->post(route('jadwal-kerja.simpan'), $dasar)->assertSessionHasErrors('tanggal_mulai');
        $this->post(route('jadwal-kerja.simpan'), [...$dasar, 'tanggal_mulai' => '2026-01-01', 'tanggal_selesai' => '2026-12-31'])->assertSessionHasErrors('tanggal_mulai');
        $jadwal = $this->jadwal();
        $this->put(route('jadwal-kerja.perbarui', $jadwal), ['libur' => '1', 'keterangan' => 'Hari libur'])->assertSessionHasNoErrors();
        $this->assertTrue($jadwal->fresh()->libur);
        $this->assertNull($jadwal->fresh()->mulai);
        $this->delete(route('jadwal-kerja.hapus', $jadwal))->assertSessionHasNoErrors();
        $this->assertDatabaseCount('jadwal_kerja', 0);
    }

    public function test_absensi_terlambat_koreksi_versi_dan_jejak_audit(): void
    {
        $jadwal = $this->jadwal();
        $data = ['masuk' => '2026-11-02T08:15', 'pulang' => '2026-11-02T15:30', 'versi' => 0, 'keterangan' => 'Koreksi mesin absensi'];
        $this->post(route('absensi.simpan', $jadwal), $data)->assertSessionHasNoErrors();
        $absensi = Absensi::query()->firstOrFail();
        $this->assertSame(15, $absensi->terlambat);
        $this->assertSame(30, $absensi->pulang_awal);
        $this->assertSame(435, $absensi->menit_kerja);
        $this->post(route('absensi.simpan', $jadwal), $data)->assertSessionHasErrors('versi');
        $this->post(route('absensi.simpan', $jadwal), [...$data, 'versi' => 1, 'pulang' => '2026-11-02T16:00'])->assertSessionHasNoErrors();
        $this->assertSame(0, $absensi->fresh()->pulang_awal);
        $this->assertDatabaseCount('riwayat_absensi', 2);
        $this->assertDatabaseHas('riwayat_absensi', ['masuk_lama' => '2026-11-02 08:15:00', 'pulang_lama' => '2026-11-02 15:30:00']);
        $this->get(route('absensi.daftar', ['catat' => $jadwal->id]))->assertOk()->assertSee('Jejak Pencatatan');
        $this->put(route('jadwal-kerja.perbarui', $jadwal), ['libur' => '1'])->assertSessionHasErrors('jadwal');
        $this->delete(route('jadwal-kerja.hapus', $jadwal))->assertSessionHasErrors('jadwal');
    }

    public function test_absensi_malam_belum_pulang_dan_waktu_tidak_valid(): void
    {
        $this->shift->update(['jam_masuk' => '22:00', 'jam_pulang' => '06:00']);
        $jadwal = $this->jadwal();
        $data = ['masuk' => '2026-11-02T22:10', 'versi' => 0, 'keterangan' => 'Pencatatan manual shift malam'];
        $this->post(route('absensi.simpan', $jadwal), $data)->assertSessionHasNoErrors();
        $this->assertDatabaseHas('absensi', ['terlambat' => 10, 'pulang' => null, 'menit_kerja' => 0]);
        $this->post(route('absensi.simpan', $jadwal), [...$data, 'versi' => 1, 'pulang' => '2026-11-03T06:00'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('absensi', ['menit_kerja' => 470, 'pulang_awal' => 0]);
        $this->post(route('absensi.simpan', $jadwal), [...$data, 'versi' => 2, 'pulang' => '2026-11-08T06:00'])->assertSessionHasErrors('masuk');
        $this->post(route('absensi.simpan', $jadwal), [...$data, 'versi' => 2, 'masuk' => '2026-11-01T22:10'])->assertSessionHasErrors('masuk');
    }

    public function test_cuti_menghitung_hari_kerja_persetujuan_dan_pembatalan_idempoten(): void
    {
        $this->jadwal();
        $this->jadwal('2026-11-03', true);
        $this->jadwal('2026-11-04');
        $pengajuan = $this->pengajuan('cuti', '2026-11-02', '2026-11-04');
        $this->assertSame(2, $pengajuan->jumlah_hari);
        $this->assertSame(12, $this->pegawai->fresh()->sisa_cuti);
        $this->putuskan($pengajuan);
        $this->assertSame(10, $this->pegawai->fresh()->sisa_cuti);
        $this->post(route('ketidakhadiran.keputusan', $pengajuan), ['keputusan' => 'disetujui', 'catatan_keputusan' => 'Persetujuan ulang'])->assertSessionHasErrors('status');
        $this->assertSame(10, $this->pegawai->fresh()->sisa_cuti);
        $this->putuskan($pengajuan, 'dibatalkan');
        $this->assertSame(12, $this->pegawai->fresh()->sisa_cuti);
        $this->post(route('ketidakhadiran.keputusan', $pengajuan), ['keputusan' => 'dibatalkan', 'catatan_keputusan' => 'Pembatalan ulang'])->assertSessionHasErrors('status');
        $this->assertSame(12, $this->pegawai->fresh()->sisa_cuti);
        $this->assertDatabaseCount('riwayat_keputusan', 2);
        $this->get(route('ketidakhadiran.daftar'))->assertOk()->assertSee('Riwayat keputusan');
    }

    public function test_saldo_diperiksa_ulang_saat_persetujuan_dan_izin_tidak_memotong_saldo(): void
    {
        $this->pegawai->update(['sisa_cuti' => 1]);
        $this->jadwal();
        $this->jadwal('2026-11-03');
        $pertama = $this->pengajuan();
        $kedua = $this->pengajuan('cuti', '2026-11-03');
        $this->putuskan($pertama);
        $this->post(route('ketidakhadiran.keputusan', $kedua), ['keputusan' => 'disetujui', 'catatan_keputusan' => 'Disetujui admin'])->assertSessionHasErrors('saldo');
        $this->assertSame('menunggu', $kedua->fresh()->status);
        $this->putuskan($kedua, 'ditolak');
        $izin = $this->pengajuan('izin', '2026-11-03');
        $this->putuskan($izin);
        $this->assertSame(0, $this->pegawai->fresh()->sisa_cuti);
    }

    public function test_pengajuan_mengunci_jadwal_menolak_duplikat_absensi_dan_periode_kosong(): void
    {
        $jadwal = $this->jadwal();
        $pengajuan = $this->pengajuan('sakit');
        $this->post(route('ketidakhadiran.simpan'), ['pegawai_id' => $this->pegawai->id, 'jenis' => 'izin', 'tanggal_mulai' => '2026-11-02', 'tanggal_selesai' => '2026-11-02', 'alasan' => 'Pengajuan duplikat'])->assertSessionHasErrors('tanggal_mulai');
        $this->post(route('absensi.simpan', $jadwal), ['masuk' => '2026-11-02T08:00', 'versi' => 0, 'keterangan' => 'Pencatatan admin'])->assertSessionHasErrors('jadwal');
        $this->put(route('jadwal-kerja.perbarui', $jadwal), ['libur' => '1'])->assertSessionHasErrors('jadwal');
        $this->delete(route('jadwal-kerja.hapus', $jadwal))->assertSessionHasErrors('jadwal');
        $this->post(route('ketidakhadiran.simpan'), ['pegawai_id' => $this->pegawai->id, 'jenis' => 'izin', 'tanggal_mulai' => '2026-11-10', 'tanggal_selesai' => '2026-11-10', 'alasan' => 'Belum ada jadwal'])->assertSessionHasErrors('tanggal_mulai');
        $this->putuskan($pengajuan, 'ditolak');
        $this->post(route('absensi.simpan', $jadwal), ['masuk' => '2026-11-02T08:00', 'versi' => 0, 'keterangan' => 'Pencatatan admin'])->assertSessionHasNoErrors();
        $this->post(route('ketidakhadiran.simpan'), ['pegawai_id' => $this->pegawai->id, 'jenis' => 'izin', 'tanggal_mulai' => '2026-11-02', 'tanggal_selesai' => '2026-11-02', 'alasan' => 'Sudah ada absensi'])->assertSessionHasErrors('tanggal_mulai');
    }

    public function test_lampiran_disimpan_privat_dan_validasi_berkas(): void
    {
        Storage::fake('local');
        $this->jadwal();
        $data = ['pegawai_id' => $this->pegawai->id, 'jenis' => 'sakit', 'tanggal_mulai' => '2026-11-02', 'tanggal_selesai' => '2026-11-02', 'alasan' => 'Surat dokter terlampir'];
        $this->post(route('ketidakhadiran.simpan'), [...$data, 'lampiran' => UploadedFile::fake()->create('script.exe', 5)])->assertSessionHasErrors('lampiran');
        $this->post(route('ketidakhadiran.simpan'), [...$data, 'lampiran' => UploadedFile::fake()->create('surat.pdf', 20, 'application/pdf')])->assertSessionHasNoErrors();
        $pengajuan = Ketidakhadiran::query()->firstOrFail();
        Storage::disk('local')->assertExists($pengajuan->lampiran);
        $this->get(route('ketidakhadiran.lampiran', $pengajuan))->assertDownload();
    }

    public function test_lembur_durasi_persetujuan_pembatalan_dan_bentrok(): void
    {
        $jadwal = $this->jadwal();
        $data = ['jadwal_kerja_id' => $jadwal->id, 'mulai' => '2026-11-02T16:00', 'selesai' => '2026-11-02T18:30', 'alasan' => 'Penyusunan laporan bulanan'];
        $this->post(route('lembur.simpan'), [...$data, 'mulai' => '2026-11-02T15:00'])->assertSessionHasErrors('mulai');
        $this->post(route('lembur.simpan'), $data)->assertSessionHasNoErrors();
        $lembur = Lembur::query()->firstOrFail();
        $this->assertSame(150, $lembur->durasi);
        $this->post(route('lembur.simpan'), $data)->assertSessionHasErrors('mulai');
        $this->post(route('lembur.keputusan', $lembur), ['keputusan' => 'disetujui', 'catatan_keputusan' => 'Pekerjaan perlu diselesaikan'])->assertSessionHasNoErrors();
        $this->put(route('jadwal-kerja.perbarui', $jadwal), ['libur' => '1'])->assertSessionHasErrors('jadwal');
        $this->post(route('lembur.keputusan', $lembur), ['keputusan' => 'disetujui', 'catatan_keputusan' => 'Keputusan ulang'])->assertSessionHasErrors('status');
        $this->post(route('lembur.keputusan', $lembur), ['keputusan' => 'dibatalkan', 'catatan_keputusan' => 'Pekerjaan dibatalkan'])->assertSessionHasNoErrors();
        $this->assertDatabaseCount('riwayat_keputusan', 2);
        $this->delete(route('jadwal-kerja.hapus', $jadwal))->assertSessionHasErrors('jadwal');
        $this->get(route('lembur.daftar', ['tanggal' => '2026-11-02']))->assertOk()->assertSee('2 jam 30 menit');
    }

    public function test_lembur_lintas_hari_menghalangi_jadwal_baru_dan_pengajuan_sakit(): void
    {
        $jadwal = $this->jadwal('2026-11-02', true);
        $this->post(route('lembur.simpan'), ['jadwal_kerja_id' => $jadwal->id, 'mulai' => '2026-11-02T23:00', 'selesai' => '2026-11-03T09:00', 'alasan' => 'Pekerjaan lintas hari'])->assertSessionHasNoErrors();
        $data = ['pegawai_id' => $this->pegawai->id, 'tanggal_mulai' => '2026-11-03', 'tanggal_selesai' => '2026-11-03', 'hari' => ['2'], 'libur' => '0', 'shift_pegawai_id' => $this->shift->id];
        $this->post(route('jadwal-kerja.simpan'), $data)->assertSessionHasErrors('jadwal');
        $this->post(route('ketidakhadiran.simpan'), ['pegawai_id' => $this->pegawai->id, 'jenis' => 'sakit', 'tanggal_mulai' => '2026-11-03', 'tanggal_selesai' => '2026-11-03', 'alasan' => 'Keperluan surat dokter'])->assertSessionHasErrors('tanggal_mulai');
    }

    public function test_laporan_rekap_filter_csv_dan_jadwal_masa_depan_bukan_alpa(): void
    {
        $jadwal = $this->jadwal();
        $this->post(route('absensi.simpan', $jadwal), ['masuk' => '2026-11-02T08:15', 'pulang' => '2026-11-02T16:00', 'versi' => 0, 'keterangan' => 'Pencatatan untuk laporan'])->assertSessionHasNoErrors();
        $this->jadwal('2026-11-03');
        $this->putuskan($this->pengajuan('cuti', '2026-11-03'));
        $this->jadwal('2026-11-04');
        $this->jadwal('2026-11-05', true);
        $this->jadwal('2026-11-09');
        $this->post(route('lembur.simpan'), ['jadwal_kerja_id' => $jadwal->id, 'mulai' => '2026-11-02T16:00', 'selesai' => '2026-11-02T18:00', 'alasan' => 'Lembur untuk laporan'])->assertSessionHasNoErrors();
        $this->post(route('lembur.keputusan', Lembur::query()->firstOrFail()), ['keputusan' => 'disetujui', 'catatan_keputusan' => 'Disetujui untuk laporan'])->assertSessionHasNoErrors();
        $parameter = ['tanggal_mulai' => '2026-11-01', 'tanggal_selesai' => '2026-11-30', 'pegawai_id' => $this->pegawai->id, 'kantor_id' => $this->pegawai->kantor_id];
        $this->get(route('laporan-hr.daftar', $parameter))->assertOk()->assertViewHas('rekap', function ($rekap): bool {
            $baris = $rekap->first();

            return $baris['hari_kerja'] === 4 && $baris['hadir'] === 1 && $baris['cuti'] === 1 && $baris['tanpa_keterangan'] === 1 && $baris['belum_berakhir'] === 1 && $baris['libur'] === 1 && $baris['terlambat'] === 15 && $baris['menit_kerja'] === 465 && $baris['lembur'] === 120;
        });
        $this->pegawai->update(['nama_lengkap' => '=FORMULA()']);
        $unduhan = $this->get(route('laporan-hr.daftar', [...$parameter, 'ekspor' => 'csv']))->assertDownload('laporan-hr-2026-11-01-2026-11-30.csv')->streamedContent();
        $this->assertStringContainsString("'=FORMULA()", $unduhan);
        $this->assertStringContainsString('00123', $unduhan);
        $barisCsv = explode("\n", trim(substr($unduhan, 3)));
        $this->assertCount(17, str_getcsv($barisCsv[0], ';', '"', ''));
        $this->assertCount(17, str_getcsv($barisCsv[1], ';', '"', ''));
        $this->get(route('laporan-hr.daftar', ['tanggal_mulai' => '2026-12-01', 'tanggal_selesai' => '2026-11-01']))->assertSessionHasErrors('tanggal_selesai');
    }

    public function test_akses_modul_dibatasi_dan_status_input_tidak_dapat_dipalsukan(): void
    {
        $this->jadwal();
        $this->post(route('ketidakhadiran.simpan'), ['pegawai_id' => $this->pegawai->id, 'jenis' => 'izin', 'tanggal_mulai' => '2026-11-02', 'tanggal_selesai' => '2026-11-02', 'alasan' => 'Pengajuan izin pegawai', 'status' => 'disetujui', 'jumlah_hari' => 0])->assertSessionHasNoErrors();
        $pengajuan = Ketidakhadiran::query()->firstOrFail();
        $this->assertSame('menunggu', $pengajuan->status);
        $this->assertSame(1, $pengajuan->jumlah_hari);
        $role = Role::query()->create(['nama' => 'staff umum', 'status' => true]);
        $this->actingAs(User::factory()->create(['role_id' => $role->id, 'status' => true]));
        foreach (['jadwal-kerja', 'absensi', 'cuti-izin-sakit', 'lembur', 'laporan-hr'] as $jalur) {
            $this->get('/administrator/'.$jalur)->assertForbidden();
        }
        $this->post(route('ketidakhadiran.keputusan', $pengajuan), ['keputusan' => 'disetujui', 'catatan_keputusan' => 'Percobaan akses'])->assertForbidden();
        auth()->logout();
        $this->get(route('laporan-hr.daftar'))->assertRedirect(route('login'));
    }
}

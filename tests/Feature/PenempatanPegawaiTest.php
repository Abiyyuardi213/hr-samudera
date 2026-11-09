<?php

namespace Tests\Feature;

use App\Models\Jabatan;
use App\Models\Kantor;
use App\Models\Kota;
use App\Models\Pegawai;
use App\Models\PenempatanPegawai;
use App\Models\Role;
use App\Models\ShiftPegawai;
use App\Models\StatusPegawai;
use App\Models\UnitOrganisasi;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PenempatanPegawaiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(Carbon::parse('2026-11-07 10:00:00', 'Asia/Jakarta'));
    }

    /** @return array{0: Pegawai, 1: array<string, mixed>} */
    private function pegawai(bool $melaluiForm = true): array
    {
        $role = Role::query()->create(['nama' => 'admin', 'status' => true]);
        $this->actingAs(User::factory()->create(['role_id' => $role->id, 'status' => true]));
        $unit = UnitOrganisasi::query()->create(['nama' => 'Divisi SDM', 'jenis_unit' => 'divisi']);
        $jabatan = Jabatan::query()->create(['nama' => 'Staf SDM', 'unit_organisasi_id' => $unit->id]);
        $kota = Kota::query()->create(['kode' => '32.73', 'nama' => 'Kota Bandung', 'kode_provinsi' => '32', 'nama_provinsi' => 'Jawa Barat', 'sinkron_pada' => now()]);
        $kantor = Kantor::query()->create(['nama_kantor' => 'Kantor Lama', 'kota_id' => $kota->id, 'alamat_lengkap' => 'Jalan Contoh', 'nomor_telepon' => '0221234567', 'email' => 'kantor@example.com']);
        $status = StatusPegawai::query()->create(['nama' => 'Kontrak']);
        $shift = ShiftPegawai::query()->create(['nama_shift' => 'Pagi', 'jam_masuk' => '08:00', 'jam_pulang' => '16:00', 'wajib_qr' => true]);
        $data = ['nama_lengkap' => 'Pegawai Riwayat', 'nip' => '00001', 'jabatan_id' => $jabatan->id, 'divisi_id' => $unit->id, 'kantor_id' => $kantor->id, 'status_pegawai_id' => $status->id, 'shift_pegawai_id' => $shift->id, 'nik' => '0123456789012345', 'tempat_lahir' => 'Bandung', 'tanggal_lahir' => '1990-01-01', 'tanggal_masuk' => '2025-01-01', 'jenis_kelamin' => 'laki_laki', 'alamat_domisili' => 'Jalan Domisili', 'sisa_cuti' => 12, 'nomor_telepon' => '08123456789', 'email' => 'pegawai@example.com'];
        if ($melaluiForm) {
            $this->post('/administrator/pegawai', $data)->assertSessionHasNoErrors();
            $pegawai = Pegawai::query()->firstOrFail();
        } else {
            $pegawai = Pegawai::query()->create($data);
        }

        return [$pegawai, $data];
    }

    /** @return array<string, mixed> */
    private function tujuan(Pegawai $pegawai): array
    {
        $kantor = Kantor::query()->create(['nama_kantor' => 'Kantor Baru', 'kota_id' => $pegawai->kantor->kota_id, 'alamat_lengkap' => 'Jalan Baru', 'nomor_telepon' => '0229999999', 'email' => 'baru@example.com']);

        return ['pegawai_id' => $pegawai->id, 'versi_penempatan' => $pegawai->riwayatPenempatan()->latest('tanggal_mulai')->value('id'), 'jabatan_id' => $pegawai->jabatan_id, 'kantor_id' => $kantor->id, 'status_pegawai_id' => $pegawai->status_pegawai_id, 'shift_pegawai_id' => $pegawai->shift_pegawai_id, 'jenis' => 'mutasi', 'tanggal_mulai' => '2026-10-01', 'nomor_sk' => 'SK/001/2026', 'keterangan' => 'Mutasi penempatan ke kantor baru.'];
    }

    public function test_penempatan_awal_otomatis_dan_halaman_riwayat(): void
    {
        [$pegawai, $data] = $this->pegawai();
        $awal = $pegawai->riwayatPenempatan()->firstOrFail();
        $this->assertSame('awal', $awal->jenis);
        $this->assertSame('2025-01-01', $awal->tanggal_mulai->toDateString());
        $this->assertSame('Kantor Lama', $awal->nama_kantor);
        $this->assertSame(480, $awal->durasi_shift);
        $this->assertTrue($awal->wajib_qr);
        $this->get('/administrator/penempatan-pegawai')->assertOk()->assertSee('modalPenempatan')->assertSee('Pegawai Riwayat');
        $this->get('/administrator/penempatan-pegawai?pegawai_id='.$pegawai->id)->assertOk()->assertSee('Penempatan saat ini');
        $this->get('/administrator/penempatan-pegawai/'.$awal->id)->assertOk()->assertSee('Kantor Lama');
        $this->put('/administrator/pegawai/'.$pegawai->id, [...$data, 'nama_lengkap' => 'Nama Diperbarui'])->assertSessionHasNoErrors();
        $this->assertDatabaseCount('penempatan_pegawai', 1);
    }

    public function test_mutasi_menutup_periode_lama_dan_memperbarui_data_aktif(): void
    {
        [$pegawai] = $this->pegawai();
        $data = $this->tujuan($pegawai);
        $this->post('/administrator/penempatan-pegawai', [...$data, 'divisi_id' => 'PALSU'])->assertRedirect(route('penempatan-pegawai.daftar', ['pegawai_id' => $pegawai->id]));
        $this->assertDatabaseCount('penempatan_pegawai', 2);
        $awal = $pegawai->riwayatPenempatan()->where('jenis', 'awal')->firstOrFail();
        $baru = $pegawai->riwayatPenempatan()->where('jenis', 'mutasi')->firstOrFail();
        $this->assertSame('2026-09-30', $awal->tanggal_selesai->toDateString());
        $this->assertNull($baru->tanggal_selesai);
        $this->assertSame($data['kantor_id'], $pegawai->refresh()->kantor_id);
        $this->assertSame('Divisi SDM', $baru->nama_divisi);
        $this->assertSame(auth()->id(), $baru->pencatat_id);
        $this->get('/administrator/penempatan-pegawai/'.$baru->id)->assertOk()->assertSee('Kantor Lama')->assertSee('Kantor Baru')->assertSee('SK/001/2026');
        $this->get('/administrator/penempatan-pegawai?jenis=mutasi&cari=SK%2F001')->assertOk()->assertViewHas('daftarRiwayat', fn ($daftar): bool => $daftar->total() === 1);
    }

    public function test_tanggal_tidak_valid_dan_penempatan_sama_ditolak(): void
    {
        [$pegawai] = $this->pegawai();
        $data = $this->tujuan($pegawai);
        foreach (['2024-12-31', '2025-01-01', '2026-11-08'] as $tanggal) {
            $this->post('/administrator/penempatan-pegawai', [...$data, 'tanggal_mulai' => $tanggal])->assertSessionHasErrors('tanggal_mulai');
        }
        $this->post('/administrator/penempatan-pegawai', [...$data, 'kantor_id' => $pegawai->kantor_id])->assertSessionHasErrors('jabatan_id');
        $this->assertDatabaseCount('penempatan_pegawai', 1);
        $this->assertNull($pegawai->riwayatPenempatan()->firstOrFail()->tanggal_selesai);
    }

    public function test_form_kedaluwarsa_dan_pengiriman_ulang_tidak_menggandakan_riwayat(): void
    {
        [$pegawai] = $this->pegawai();
        $data = $this->tujuan($pegawai);
        $this->post('/administrator/penempatan-pegawai', $data)->assertSessionHasNoErrors();
        $this->post('/administrator/penempatan-pegawai', [...$data, 'tanggal_mulai' => '2026-11-01'])->assertSessionHasErrors('pegawai_id');
        $this->assertDatabaseCount('penempatan_pegawai', 2);
    }

    public function test_penempatan_hari_ini_dan_perubahan_shift_status_jabatan(): void
    {
        [$pegawai] = $this->pegawai();
        $data = $this->tujuan($pegawai);
        $divisi = UnitOrganisasi::query()->create(['nama' => 'Divisi Keuangan', 'jenis_unit' => 'divisi']);
        $jabatan = Jabatan::query()->create(['nama' => 'Kepala Keuangan', 'unit_organisasi_id' => $divisi->id]);
        $shift = ShiftPegawai::query()->create(['nama_shift' => 'Malam', 'jam_masuk' => '22:00', 'jam_pulang' => '06:00', 'wajib_qr' => false]);
        $status = StatusPegawai::query()->create(['nama' => 'Tetap']);
        $this->post('/administrator/penempatan-pegawai', [...$data, 'tanggal_mulai' => '2026-11-07', 'jenis' => 'promosi', 'jabatan_id' => $jabatan->id, 'shift_pegawai_id' => $shift->id, 'status_pegawai_id' => $status->id])->assertSessionHasNoErrors();
        $pegawai->refresh();
        $this->assertSame($divisi->id, $pegawai->divisi_id);
        $this->assertSame($shift->id, $pegawai->shift_pegawai_id);
        $this->assertSame($status->id, $pegawai->status_pegawai_id);
        $terakhir = $pegawai->riwayatPenempatan()->latest('tanggal_mulai')->firstOrFail();
        $this->assertSame('Divisi Keuangan', $terakhir->nama_divisi);
        $this->assertFalse($terakhir->wajib_qr);
    }

    public function test_nama_master_lama_tetap_tersimpan_dan_referensi_riwayat_dilindungi(): void
    {
        [$pegawai] = $this->pegawai();
        $kantorLama = $pegawai->kantor;
        $data = $this->tujuan($pegawai);
        $this->post('/administrator/penempatan-pegawai', $data)->assertSessionHasNoErrors();
        $kantorLama->update(['nama_kantor' => 'Nama Diubah']);
        $awal = $pegawai->riwayatPenempatan()->where('jenis', 'awal')->firstOrFail();
        $this->assertSame('Kantor Lama', $awal->nama_kantor);
        $this->delete('/administrator/kantor/'.$kantorLama->id)->assertSessionHasErrors('pegawai');
        $this->delete('/administrator/pegawai/'.$pegawai->id)->assertSessionHasErrors('pegawai');
        $this->assertDatabaseCount('penempatan_pegawai', 2);
    }

    public function test_form_pegawai_tidak_bisa_melewati_pencatatan_riwayat(): void
    {
        [$pegawai, $data] = $this->pegawai();
        $tujuan = $this->tujuan($pegawai);
        $this->put('/administrator/pegawai/'.$pegawai->id, [...$data, 'kantor_id' => $tujuan['kantor_id']])->assertSessionHasErrors('kantor_id');
        $this->put('/administrator/pegawai/'.$pegawai->id, [...$data, 'tanggal_masuk' => '2024-01-01'])->assertSessionHasErrors('tanggal_masuk');
        $this->assertSame($data['kantor_id'], $pegawai->refresh()->kantor_id);
        $this->assertDatabaseCount('penempatan_pegawai', 1);
    }

    public function test_transaksi_mengembalikan_seluruh_perubahan_jika_pencatatan_gagal(): void
    {
        [$pegawai] = $this->pegawai();
        $kantorAwal = $pegawai->kantor_id;
        $data = $this->tujuan($pegawai);
        $data['divisi_id'] = $pegawai->divisi_id;
        try {
            PenempatanPegawai::catat($pegawai, $data, 999999);
            $this->fail('Pencatat yang tidak ditemukan seharusnya menggagalkan transaksi.');
        } catch (ModelNotFoundException) {
            $this->assertSame($kantorAwal, $pegawai->refresh()->kantor_id);
            $this->assertDatabaseCount('penempatan_pegawai', 1);
            $this->assertNull($pegawai->riwayatPenempatan()->firstOrFail()->tanggal_selesai);
        }
    }

    public function test_migrasi_membuat_riwayat_awal_untuk_pegawai_lama(): void
    {
        [$pegawai] = $this->pegawai(false);
        $migrasi = require database_path('migrations/2026_11_07_000000_buat_tabel_penempatan_pegawai.php');
        $migrasi->down();
        $migrasi->up();
        $this->assertDatabaseHas('penempatan_pegawai', ['pegawai_id' => $pegawai->id, 'jenis' => 'awal', 'nama_kantor' => 'Kantor Lama', 'nama_divisi' => 'Divisi SDM']);
    }

    public function test_akses_dan_validasi_form(): void
    {
        $this->get('/administrator/penempatan-pegawai')->assertRedirect(route('login'));
        [$pegawai] = $this->pegawai();
        $this->post('/administrator/penempatan-pegawai', [])->assertSessionHasErrors(['pegawai_id', 'jabatan_id', 'kantor_id', 'status_pegawai_id', 'shift_pegawai_id', 'jenis', 'tanggal_mulai', 'keterangan']);
        $riwayat = $pegawai->riwayatPenempatan()->firstOrFail();
        $this->put('/administrator/penempatan-pegawai/'.$riwayat->id, [])->assertStatus(405);
        $this->delete('/administrator/penempatan-pegawai/'.$riwayat->id)->assertStatus(405);
        $role = Role::query()->create(['nama' => 'staff umum', 'status' => true]);
        $this->actingAs(User::factory()->create(['role_id' => $role->id, 'status' => true]));
        $this->get('/administrator/penempatan-pegawai')->assertForbidden();
        $this->get('/administrator/penempatan-pegawai/'.$riwayat->id)->assertForbidden();
        $this->post('/administrator/penempatan-pegawai', [])->assertForbidden();
    }
}

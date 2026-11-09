<?php

namespace Tests\Feature;

use App\Models\Jabatan;
use App\Models\Kantor;
use App\Models\Kota;
use App\Models\Pegawai;
use App\Models\Role;
use App\Models\ShiftPegawai;
use App\Models\StatusPegawai;
use App\Models\UnitOrganisasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PegawaiTest extends TestCase
{
    use RefreshDatabase;

    private function masuk(): void
    {
        $role = Role::query()->create(['nama' => 'admin', 'status' => true]);
        $this->actingAs(User::factory()->create(['role_id' => $role->id, 'status' => true]));
    }

    /** @return array<string, mixed> */
    private function data(): array
    {
        $divisi = UnitOrganisasi::query()->create(['nama' => 'Human Capital', 'jenis_unit' => 'divisi']);
        $departemen = UnitOrganisasi::query()->create(['nama' => 'Administrasi SDM', 'jenis_unit' => 'departemen', 'induk_id' => $divisi->id]);
        $jabatan = Jabatan::query()->create(['nama' => 'Staf HR', 'unit_organisasi_id' => $departemen->id]);
        $kota = Kota::query()->create(['kode' => '32.73', 'nama' => 'Kota Bandung', 'kode_provinsi' => '32', 'nama_provinsi' => 'Jawa Barat', 'sinkron_pada' => now()]);
        $kantor = Kantor::query()->create(['nama_kantor' => 'Kantor Bandung', 'kota_id' => $kota->id, 'alamat_lengkap' => 'Jalan Kantor', 'nomor_telepon' => '0221234567', 'email' => 'kantor@example.com']);
        $status = StatusPegawai::query()->create(['nama' => 'Tetap']);
        $shift = ShiftPegawai::query()->create(['nama_shift' => 'Pagi', 'jam_masuk' => '08:00', 'jam_pulang' => '16:00', 'wajib_qr' => false]);

        return ['nama_lengkap' => 'Pegawai Contoh', 'nip' => '000123', 'jabatan_id' => $jabatan->id, 'kantor_id' => $kantor->id, 'status_pegawai_id' => $status->id, 'nik' => '0123456789012345', 'tempat_lahir' => 'Bandung', 'tanggal_lahir' => '1995-02-20', 'jenis_kelamin' => 'perempuan', 'alamat_domisili' => 'Jalan Domisili No. 1', 'tanggal_masuk' => '2025-01-01', 'shift_pegawai_id' => $shift->id, 'sisa_cuti' => 12, 'nomor_telepon' => '08123456789', 'email' => 'pegawai@example.com'];
    }

    public function test_crud_dan_divisi_otomatis_dari_hierarki_jabatan(): void
    {
        $this->masuk();
        $data = $this->data();
        $this->get('/administrator/pegawai')->assertOk()->assertSee('modalFormulirPegawai');
        $this->get('/administrator/pegawai/tambah')->assertRedirect(route('pegawai.daftar', ['tambah' => 1]));
        $this->post('/administrator/pegawai', [...$data, 'divisi_id' => 'PALSU', 'id' => 'PALSU'])->assertRedirect(route('pegawai.daftar'));
        $pegawai = Pegawai::query()->firstOrFail();
        $this->assertTrue(Str::isUuid($pegawai->id));
        $this->assertSame('000123', $pegawai->nip);
        $this->assertSame('0123456789012345', $pegawai->nik);
        $this->assertSame('Human Capital', $pegawai->divisi->nama);
        $this->get('/administrator/pegawai/'.$pegawai->id)->assertOk()->assertSee('0123456789012345')->assertSee('Human Capital');
        $this->get('/administrator/pegawai?cari=000123')->assertOk()->assertSee('Pegawai Contoh');
        $this->get('/administrator/pegawai/'.$pegawai->id.'/ubah')->assertRedirect(route('pegawai.daftar', ['ubah' => $pegawai->id]));
        $this->get('/administrator/pegawai?ubah='.$pegawai->id)->assertOk()->assertSee('Ubah Pegawai')->assertSee('value="1995-02-20"', false);
        $this->put('/administrator/pegawai/'.$pegawai->id, [...$data, 'nama_lengkap' => 'Pegawai Diubah', 'sisa_cuti' => 0])->assertRedirect(route('pegawai.daftar'));
        $this->assertDatabaseHas('pegawai', ['id' => $pegawai->id, 'nama_lengkap' => 'Pegawai Diubah', 'sisa_cuti' => 0]);
        $this->delete('/administrator/pegawai/'.$pegawai->id)->assertSessionHasErrors('pegawai');
        $this->assertDatabaseCount('pegawai', 1);
    }

    public function test_duplikasi_identitas_dan_data_tidak_valid_ditolak(): void
    {
        $this->masuk();
        $data = $this->data();
        $this->post('/administrator/pegawai', $data)->assertSessionHasNoErrors();
        $this->post('/administrator/pegawai', $data)->assertSessionHasErrors(['nip', 'nik', 'email']);
        $this->post('/administrator/pegawai', [...$data, 'nik' => '123', 'tanggal_lahir' => '2999-01-01', 'tanggal_masuk' => '1990-01-01', 'jenis_kelamin' => 'salah', 'sisa_cuti' => -1, 'nomor_telepon' => 'abc', 'jabatan_id' => (string) Str::uuid()])->assertSessionHasErrors(['nik', 'tanggal_lahir', 'tanggal_masuk', 'jenis_kelamin', 'sisa_cuti', 'nomor_telepon', 'jabatan_id']);
        $this->assertDatabaseCount('pegawai', 1);
    }

    public function test_master_yang_dipakai_pegawai_tidak_bisa_dihapus(): void
    {
        $this->masuk();
        $data = $this->data();
        $this->post('/administrator/pegawai', $data)->assertSessionHasNoErrors();
        $pegawai = Pegawai::query()->firstOrFail();
        foreach (['jabatan' => $pegawai->jabatan_id, 'kantor' => $pegawai->kantor_id, 'status-pegawai' => $pegawai->status_pegawai_id, 'shift-pegawai' => $pegawai->shift_pegawai_id, 'unit-organisasi' => $pegawai->divisi_id] as $modul => $id) {
            $this->delete('/administrator/'.$modul.'/'.$id)->assertSessionHasErrors('pegawai');
        }
        $this->assertDatabaseCount('pegawai', 1);
    }

    public function test_jabatan_di_atas_divisi_bisa_tanpa_divisi(): void
    {
        $this->masuk();
        $data = $this->data();
        $unit = UnitOrganisasi::query()->create(['nama' => 'Direktorat SDM', 'jenis_unit' => 'direktorat']);
        $jabatan = Jabatan::query()->create(['nama' => 'Direktur SDM', 'unit_organisasi_id' => $unit->id]);
        $this->post('/administrator/pegawai', [...$data, 'jabatan_id' => $jabatan->id])->assertSessionHasNoErrors();
        $this->assertNull(Pegawai::query()->firstOrFail()->divisi_id);
    }

    public function test_tamu_dan_staff_tidak_dapat_membuka_data_pribadi(): void
    {
        $this->get('/administrator/pegawai')->assertRedirect(route('login'));
        $data = $this->data();
        $pegawai = Pegawai::query()->create($data);
        $role = Role::query()->create(['nama' => 'staff umum', 'status' => true]);
        $this->actingAs(User::factory()->create(['role_id' => $role->id, 'status' => true]));
        $this->get('/administrator/pegawai')->assertForbidden();
        $this->get('/administrator/pegawai/'.$pegawai->id)->assertForbidden();
        $this->post('/administrator/pegawai', $data)->assertForbidden();
    }
}

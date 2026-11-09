<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\StatusPegawai;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class StatusPegawaiTest extends TestCase
{
    use RefreshDatabase;

    private function masuk(): void
    {
        $role = Role::query()->create(['nama' => 'admin', 'status' => true]);
        $this->actingAs(User::factory()->create(['role_id' => $role->id, 'status' => true]));
    }

    public function test_crud_status_uuid_dan_kode_otomatis(): void
    {
        $this->masuk();
        $this->get('/administrator/status-pegawai')->assertOk()->assertSee('modalFormulirStatus');
        $this->get('/administrator/status-pegawai/tambah')->assertRedirect(route('status-pegawai.daftar', ['tambah' => 1]));
        $this->post('/administrator/status-pegawai', ['nama' => 'Pegawai Tetap', 'deskripsi' => 'Pegawai tetap perusahaan', 'kode' => 'PALSU', 'id' => 'PALSU'])->assertRedirect(route('status-pegawai.daftar'));
        $statusPegawai = StatusPegawai::query()->firstOrFail();
        $this->assertTrue(Str::isUuid($statusPegawai->id));
        $this->assertStringStartsWith('STP-PEGAWAI-TETAP-', $statusPegawai->kode);
        $kodeAwal = $statusPegawai->kode;
        $this->get('/administrator/status-pegawai/'.$statusPegawai->id.'/ubah')->assertRedirect(route('status-pegawai.daftar', ['ubah' => $statusPegawai->id]));
        $this->get('/administrator/status-pegawai?ubah='.$statusPegawai->id)->assertOk()->assertSee('Ubah Status Pegawai');
        $this->put('/administrator/status-pegawai/'.$statusPegawai->id, ['nama' => 'Tetap', 'deskripsi' => '', 'kode' => 'PALSU'])->assertRedirect(route('status-pegawai.daftar'));
        $this->assertSame($kodeAwal, $statusPegawai->refresh()->kode);
        $this->assertNull($statusPegawai->deskripsi);
        $this->get('/administrator/status-pegawai?cari='.$kodeAwal)->assertOk()->assertSee('Tetap');
        $this->delete('/administrator/status-pegawai/'.$statusPegawai->id)->assertRedirect(route('status-pegawai.daftar'));
        $this->assertDatabaseCount('status_pegawai', 0);
    }

    public function test_nama_sama_menghasilkan_kode_unik(): void
    {
        $pertama = StatusPegawai::query()->create(['nama' => 'Kontrak']);
        $kedua = StatusPegawai::query()->create(['nama' => 'Kontrak']);
        $this->assertNotSame($pertama->kode, $kedua->kode);
    }

    public function test_validasi_dan_pembatasan_akses(): void
    {
        $this->get('/administrator/status-pegawai')->assertRedirect(route('login'));
        $this->masuk();
        $this->post('/administrator/status-pegawai', ['deskripsi' => []])->assertSessionHasErrors(['nama', 'deskripsi']);
        $this->assertDatabaseCount('status_pegawai', 0);
        $role = Role::query()->create(['nama' => 'staff HR', 'status' => true]);
        $this->actingAs(User::factory()->create(['role_id' => $role->id, 'status' => true]));
        $this->get('/administrator/status-pegawai')->assertForbidden();
        $this->post('/administrator/status-pegawai', [])->assertForbidden();
    }
}

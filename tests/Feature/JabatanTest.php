<?php

namespace Tests\Feature;

use App\Models\Jabatan;
use App\Models\Role;
use App\Models\UnitOrganisasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class JabatanTest extends TestCase
{
    use RefreshDatabase;

    private function masuk(): UnitOrganisasi
    {
        $role = Role::query()->create(['nama' => 'admin', 'status' => true]);
        $this->actingAs(User::factory()->create(['role_id' => $role->id, 'status' => true]));

        return UnitOrganisasi::query()->create(['nama' => 'Direktorat SDM', 'jenis_unit' => 'direktorat', 'status' => true]);
    }

    public function test_crud_dan_relasi_jabatan(): void
    {
        $unit = $this->masuk();
        $data = ['nama' => 'Direktur SDM', 'unit_organisasi_id' => $unit->id, 'atasan_id' => '', 'deskripsi' => 'Memimpin SDM', 'status' => 1];
        $this->get('/administrator/jabatan')->assertOk()->assertSee('modalFormulirJabatan');
        $this->get('/administrator/jabatan/tambah')->assertRedirect(route('jabatan.daftar', ['tambah' => 1]));
        $this->post('/administrator/jabatan', $data)->assertRedirect(route('jabatan.daftar'));
        $jabatan = Jabatan::query()->firstOrFail();
        $this->assertTrue(Str::isUuid($jabatan->id));
        $this->assertSame($unit->id, $jabatan->unitOrganisasi->id);
        $this->get('/administrator/jabatan/'.$jabatan->id)->assertOk()->assertSee('Memimpin SDM')->assertSee('Direktorat SDM');
        $this->get('/administrator/jabatan/'.$jabatan->id.'/ubah')->assertRedirect(route('jabatan.daftar', ['ubah' => $jabatan->id]));
        $this->get('/administrator/jabatan?ubah='.$jabatan->id)->assertOk()->assertSee('Ubah Jabatan');
        $this->put('/administrator/jabatan/'.$jabatan->id, [...$data, 'nama' => 'Direktur Human Capital', 'status' => 0])->assertRedirect(route('jabatan.daftar'));
        $this->assertDatabaseHas('jabatan', ['id' => $jabatan->id, 'status' => false]);
        $this->get('/administrator/jabatan?cari=Direktorat')->assertOk()->assertSee('Direktur Human Capital');
        $this->delete('/administrator/jabatan/'.$jabatan->id)->assertRedirect(route('jabatan.daftar'));
        $this->assertDatabaseCount('jabatan', 0);
    }

    public function test_atasan_melingkar_dan_penghapusan_relasi_dicegah(): void
    {
        $unit = $this->masuk();
        $atasan = Jabatan::query()->create(['nama' => 'Direktur SDM', 'unit_organisasi_id' => $unit->id]);
        $bawahan = Jabatan::query()->create(['nama' => 'Kepala Human Capital', 'unit_organisasi_id' => $unit->id, 'atasan_id' => $atasan->id]);
        $cucu = Jabatan::query()->create(['nama' => 'Staf HR', 'unit_organisasi_id' => $unit->id, 'atasan_id' => $bawahan->id]);
        $data = ['nama' => $atasan->nama, 'unit_organisasi_id' => $unit->id, 'status' => 1];
        $this->put('/administrator/jabatan/'.$atasan->id, [...$data, 'atasan_id' => $atasan->id])->assertSessionHasErrors('atasan_id');
        $this->put('/administrator/jabatan/'.$atasan->id, [...$data, 'atasan_id' => $cucu->id])->assertSessionHasErrors('atasan_id');
        $this->delete('/administrator/jabatan/'.$atasan->id)->assertSessionHasErrors('jabatan');
        $this->delete('/administrator/unit-organisasi/'.$unit->id)->assertSessionHasErrors('unit');
        $this->get('/administrator/jabatan?ubah='.$atasan->id)->assertOk()->assertViewHas('daftarAtasan', fn ($daftar): bool => $daftar->isEmpty());
        $this->assertNull($atasan->refresh()->atasan_id);
        $this->get('/administrator/jabatan/'.$atasan->id)->assertOk()->assertSee('Kepala Human Capital');
    }

    public function test_validasi_dan_akses(): void
    {
        $this->get('/administrator/jabatan')->assertRedirect(route('login'));
        $this->masuk();
        $this->post('/administrator/jabatan', ['unit_organisasi_id' => (string) Str::uuid(), 'atasan_id' => 'salah', 'status' => 'aktif'])->assertSessionHasErrors(['nama', 'unit_organisasi_id', 'atasan_id', 'status']);
        $this->assertDatabaseCount('jabatan', 0);
        $role = Role::query()->create(['nama' => 'staff HR', 'status' => true]);
        $this->actingAs(User::factory()->create(['role_id' => $role->id, 'status' => true]));
        $this->get('/administrator/jabatan')->assertForbidden();
        $this->post('/administrator/jabatan', [])->assertForbidden();
    }
}

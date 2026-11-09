<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\UnitOrganisasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class UnitOrganisasiTest extends TestCase
{
    use RefreshDatabase;

    private function masuk(): void
    {
        $role = Role::query()->create(['nama' => 'admin', 'status' => true]);
        $this->actingAs(User::factory()->create(['role_id' => $role->id, 'status' => true]));
    }

    public function test_crud_unit_dan_form_modal(): void
    {
        $this->masuk();
        $this->get('/administrator/unit-organisasi')->assertOk()->assertSee('modalFormulirUnit');
        $this->get('/administrator/unit-organisasi/tambah')->assertRedirect(route('unit-organisasi.daftar', ['tambah' => 1]));
        $data = ['nama' => 'Direktorat SDM', 'jenis_unit' => 'direktorat', 'induk_id' => '', 'deskripsi' => 'Pengelolaan SDM', 'status' => '1'];
        $this->post('/administrator/unit-organisasi', $data)->assertRedirect(route('unit-organisasi.daftar'));
        $unit = UnitOrganisasi::query()->firstOrFail();
        $this->assertTrue(Str::isUuid($unit->id));
        $this->assertNull($unit->induk_id);
        $this->get('/administrator/unit-organisasi/'.$unit->id)->assertOk()->assertSee('Pengelolaan SDM');
        $this->get('/administrator/unit-organisasi/'.$unit->id.'/ubah')->assertRedirect(route('unit-organisasi.daftar', ['ubah' => $unit->id]));
        $this->get('/administrator/unit-organisasi?ubah='.$unit->id)->assertOk()->assertSee('Ubah Unit Organisasi');
        $this->put('/administrator/unit-organisasi/'.$unit->id, [...$data, 'nama' => 'Direktorat Sumber Daya Manusia', 'status' => '0'])->assertRedirect(route('unit-organisasi.daftar'));
        $this->assertDatabaseHas('unit_organisasi', ['id' => $unit->id, 'status' => false]);
        $this->get('/administrator/unit-organisasi?cari=Sumber')->assertOk()->assertSee('Direktorat Sumber Daya Manusia');
        $this->delete('/administrator/unit-organisasi/'.$unit->id)->assertRedirect(route('unit-organisasi.daftar'));
        $this->assertDatabaseMissing('unit_organisasi', ['id' => $unit->id]);
    }

    public function test_hierarki_melingkar_dan_penghapusan_induk_ditolak(): void
    {
        $this->masuk();
        $induk = UnitOrganisasi::query()->create(['nama' => 'Direksi', 'jenis_unit' => 'direksi']);
        $anak = UnitOrganisasi::query()->create(['nama' => 'Direktorat SDM', 'jenis_unit' => 'direktorat', 'induk_id' => $induk->id]);
        $cucu = UnitOrganisasi::query()->create(['nama' => 'Human Capital', 'jenis_unit' => 'divisi', 'induk_id' => $anak->id]);
        $data = ['nama' => 'Direksi', 'jenis_unit' => 'direksi', 'status' => 1];
        $this->put('/administrator/unit-organisasi/'.$induk->id, [...$data, 'induk_id' => $induk->id])->assertSessionHasErrors('induk_id');
        $this->put('/administrator/unit-organisasi/'.$induk->id, [...$data, 'induk_id' => $cucu->id])->assertSessionHasErrors('induk_id');
        $this->assertNull($induk->refresh()->induk_id);
        $this->delete('/administrator/unit-organisasi/'.$induk->id)->assertSessionHasErrors('unit');
        $this->get('/administrator/unit-organisasi/'.$induk->id)->assertOk()->assertSee('Direktorat SDM');
        $this->get('/administrator/unit-organisasi?ubah='.$induk->id)->assertOk()->assertViewHas('daftarInduk', fn ($daftar): bool => $daftar->isEmpty());
    }

    public function test_unit_dapat_dipindahkan_ke_induk_lain_atau_menjadi_akar(): void
    {
        $this->masuk();
        $induk = UnitOrganisasi::query()->create(['nama' => 'Direksi', 'jenis_unit' => 'direksi']);
        $unit = UnitOrganisasi::query()->create(['nama' => 'CEO Office', 'jenis_unit' => 'ceo_office']);
        $data = ['nama' => $unit->nama, 'jenis_unit' => $unit->jenis_unit, 'status' => 1];
        $this->put('/administrator/unit-organisasi/'.$unit->id, [...$data, 'induk_id' => $induk->id])->assertSessionHasNoErrors();
        $this->assertSame($induk->id, $unit->refresh()->induk_id);
        $this->put('/administrator/unit-organisasi/'.$unit->id, [...$data, 'induk_id' => ''])->assertSessionHasNoErrors();
        $this->assertNull($unit->refresh()->induk_id);
    }

    public function test_validasi_data_dan_akses_staff(): void
    {
        $this->get('/administrator/unit-organisasi')->assertRedirect(route('login'));
        $this->masuk();
        $this->post('/administrator/unit-organisasi', ['jenis_unit' => 'tidak-valid', 'induk_id' => 'salah', 'status' => 'aktif'])->assertSessionHasErrors(['nama', 'jenis_unit', 'induk_id', 'status']);
        $this->assertDatabaseCount('unit_organisasi', 0);
        $role = Role::query()->create(['nama' => 'staff HR', 'status' => true]);
        $this->actingAs(User::factory()->create(['role_id' => $role->id, 'status' => true]));
        $this->get('/administrator/unit-organisasi')->assertForbidden();
        $this->post('/administrator/unit-organisasi', [])->assertForbidden();
    }
}

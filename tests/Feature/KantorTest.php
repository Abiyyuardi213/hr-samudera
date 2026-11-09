<?php

namespace Tests\Feature;

use App\Models\Kantor;
use App\Models\Kota;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class KantorTest extends TestCase
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
        $kota = Kota::query()->create(['kode' => '32.73', 'nama' => 'Kota Bandung', 'kode_provinsi' => '32', 'nama_provinsi' => 'Jawa Barat', 'sinkron_pada' => now()]);

        return ['nama_kantor' => 'Kantor Pusat', 'kota_id' => $kota->id, 'alamat_lengkap' => 'Jalan Merdeka No. 1', 'nomor_telepon' => '+62 22 1234567', 'email' => 'kantor@example.com'];
    }

    public function test_kantor_dapat_dikelola_dengan_uuid_kode_otomatis_dan_relasi_kota(): void
    {
        $this->masuk();
        $data = $this->data();
        $this->get('/administrator/kantor')->assertOk()->assertSee('Belum ada kantor.');
        $this->get('/administrator/kantor/tambah')->assertRedirect(route('kantor.daftar', ['tambah' => 1]));
        $this->get('/administrator/kantor?tambah=1')->assertOk()->assertSee('modalTambahKantor')->assertSee('Kota Bandung');
        $this->post('/administrator/kantor', [...$data, 'kode_kantor' => 'PALSU', 'id' => 'PALSU'])->assertRedirect(route('kantor.daftar'));
        $kantor = Kantor::query()->firstOrFail();
        $this->assertTrue(Str::isUuid($kantor->id));
        $this->assertStringStartsWith('KTR-KANTOR-PUSAT-', $kantor->kode_kantor);
        $this->assertSame('Kota Bandung', $kantor->kota->nama);
        $kodeAwal = $kantor->kode_kantor;
        $this->get('/administrator/kantor/'.$kantor->id)->assertOk()->assertSee('Jalan Merdeka No. 1');
        $this->get('/administrator/kantor/'.$kantor->id.'/ubah')->assertOk()->assertSee($kodeAwal);
        $this->put('/administrator/kantor/'.$kantor->id, [...$data, 'nama_kantor' => 'Cabang Bandung', 'kode_kantor' => 'PALSU'])->assertRedirect(route('kantor.daftar'));
        $this->assertSame($kodeAwal, $kantor->refresh()->kode_kantor);
        $this->get('/administrator/kantor?cari=Bandung')->assertOk()->assertSee('Cabang Bandung');
        $this->delete('/administrator/kantor/'.$kantor->id)->assertRedirect(route('kantor.daftar'));
        $this->assertDatabaseMissing('kantor', ['id' => $kantor->id]);
        $this->get('/administrator/kantor/'.$kantor->id)->assertNotFound();
    }

    public function test_nama_kantor_yang_sama_menghasilkan_kode_berbeda(): void
    {
        $data = $this->data();
        $pertama = Kantor::query()->create($data);
        $kedua = Kantor::query()->create($data);
        $this->assertNotSame($pertama->kode_kantor, $kedua->kode_kantor);
        $this->assertDatabaseCount('kantor', 2);
    }

    public function test_validasi_dan_kebutuhan_data_kota(): void
    {
        $this->masuk();
        $this->get('/administrator/kantor?tambah=1')->assertOk()->assertSee('Sinkron kota terlebih dahulu');
        $this->post('/administrator/kantor', [])->assertSessionHasErrors(['nama_kantor', 'kota_id', 'alamat_lengkap', 'nomor_telepon', 'email']);
        $this->post('/administrator/kantor', ['nama_kantor' => 'Kantor', 'kota_id' => 999, 'alamat_lengkap' => 'Alamat', 'nomor_telepon' => 'abc', 'email' => 'salah'])->assertSessionHasErrors(['kota_id', 'nomor_telepon', 'email']);
        $this->assertDatabaseCount('kantor', 0);
        $this->get('/administrator/kantor/bukan-uuid')->assertNotFound();
    }

    public function test_tamu_dan_staff_tidak_memiliki_akses_pengelolaan_kantor(): void
    {
        $this->get('/administrator/kantor')->assertRedirect(route('login'));
        $role = Role::query()->create(['nama' => 'staff umum', 'status' => true]);
        $this->actingAs(User::factory()->create(['role_id' => $role->id, 'status' => true]));
        $this->get('/administrator/kantor')->assertForbidden();
        $this->post('/administrator/kantor', [])->assertForbidden();
    }
}

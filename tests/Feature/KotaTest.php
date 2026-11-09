<?php

namespace Tests\Feature;

use App\Models\Kota;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class KotaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
    }

    private function masuk(): void
    {
        $role = Role::query()->create(['nama' => 'admin', 'status' => true]);
        $this->actingAs(User::factory()->create(['role_id' => $role->id, 'status' => true]));
    }

    private function api(bool $gagal = false): void
    {
        Http::fake([
            '*/stats.json' => Http::response(['data' => ['total_provinces' => 2, 'total_regencies' => 2]]),
            '*/provinces.json' => Http::response(['data' => [['id' => '31', 'name' => 'Jakarta'], ['id' => '32', 'name' => 'Jawa Barat']]]),
            '*/regencies/31.json' => Http::response(['data' => [['id' => '31.71', 'name' => 'Kota Jakarta Selatan']]]),
            '*/regencies/32.json' => $gagal ? Http::response([], 503) : Http::response(['data' => [['id' => '32.73', 'name' => 'Kota Bandung']]]),
        ]);
    }

    private function langkah(): void
    {
        $proses = Cache::get('sinkron-kota:proses');
        $this->post('/administrator/kota/sinkron/lanjut', ['id' => $proses['id'], 'langkah' => $proses['selesai']])->assertOk();
    }

    public function test_sinkron_bertahap_menyimpan_semua_kota_dan_tidak_menggandakan_data(): void
    {
        $this->masuk();
        $this->api();
        $this->get('/administrator/kota')->assertOk()->assertSee('Belum ada data kota.');
        $this->post('/administrator/kota/sinkron')->assertOk()->assertSee('data-status="berjalan"', false);
        $this->langkah();
        $this->assertDatabaseCount('kota', 0);
        $this->assertSame(1, Cache::get('sinkron-kota:proses')['selesai']);
        $this->langkah();
        $this->assertDatabaseCount('kota', 2);
        $this->assertSame('berhasil', Cache::get('sinkron-kota:proses')['status']);
        $this->get('/administrator/kota')->assertOk()->assertSee('Kota Bandung')->assertSee('Jakarta');
        $this->get('/administrator/kota?cari=Bandung')->assertOk()->assertSee('Kota Bandung')->assertDontSee('Kota Jakarta Selatan');
        $this->post('/administrator/kota/sinkron')->assertOk();
        $this->langkah();
        $this->langkah();
        $this->assertDatabaseCount('kota', 2);
    }

    public function test_proses_dapat_dilanjutkan_dan_langkah_berulang_tidak_mengambil_data_dua_kali(): void
    {
        $this->masuk();
        $this->api();
        $this->post('/administrator/kota/sinkron')->assertOk();
        $proses = Cache::get('sinkron-kota:proses');
        $this->post('/administrator/kota/sinkron')->assertOk();
        Http::assertSentCount(2);
        $this->langkah();
        $this->post('/administrator/kota/sinkron/lanjut', ['id' => $proses['id'], 'langkah' => 0])->assertOk();
        Http::assertSentCount(3);
        $this->get('/administrator/kota')->assertOk()->assertSee('data-lanjut-otomatis="1"', false);
        $this->langkah();
        $this->assertDatabaseCount('kota', 2);
    }

    public function test_api_gagal_tidak_mengubah_data_yang_sudah_tersimpan(): void
    {
        $this->masuk();
        $this->api(true);
        Kota::query()->create(['kode' => '32.73', 'nama' => 'Bandung Lama', 'kode_provinsi' => '32', 'nama_provinsi' => 'Jawa Barat', 'sinkron_pada' => now()]);
        $this->post('/administrator/kota/sinkron')->assertOk();
        $this->langkah();
        $this->langkah();
        $this->assertSame('gagal', Cache::get('sinkron-kota:proses')['status']);
        $this->assertDatabaseCount('kota', 1);
        $this->assertDatabaseHas('kota', ['nama' => 'Bandung Lama']);
    }

    public function test_data_tidak_lengkap_atau_format_salah_ditolak(): void
    {
        $this->masuk();
        Http::fake(['*' => Http::response(['data' => ['tidak_valid']])]);
        $this->post('/administrator/kota/sinkron')->assertOk()->assertSee('data-status="gagal"', false);
        $this->assertDatabaseCount('kota', 0);
    }

    public function test_sinkron_dengan_jumlah_wilayah_tidak_sesuai_tidak_disimpan(): void
    {
        $this->masuk();
        $this->api();
        $this->post('/administrator/kota/sinkron')->assertOk();
        $proses = Cache::get('sinkron-kota:proses');
        $proses['target'] = 3;
        Cache::put('sinkron-kota:proses', $proses, 3600);
        $this->langkah();
        $this->langkah();
        $this->assertSame('gagal', Cache::get('sinkron-kota:proses')['status']);
        $this->assertDatabaseCount('kota', 0);
    }

    public function test_tamu_dan_staff_tidak_dapat_melakukan_sinkron(): void
    {
        $this->post('/administrator/kota/sinkron')->assertRedirect(route('login'));
        $role = Role::query()->create(['nama' => 'staff HR', 'status' => true]);
        $this->actingAs(User::factory()->create(['role_id' => $role->id, 'status' => true]));
        $this->get('/administrator/kota')->assertForbidden();
        $this->post('/administrator/kota/sinkron')->assertForbidden();
        Http::assertNothingSent();
    }
}

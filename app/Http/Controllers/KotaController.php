<?php

namespace App\Http\Controllers;

use App\Models\Kota;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class KotaController extends Controller
{
    private const KUNCI = 'sinkron-kota:proses';

    public function daftar(Request $permintaan): View
    {
        $pencarian = $permintaan->validate(['cari' => ['nullable', 'string', 'max:100']])['cari'] ?? '';
        $daftarKota = Kota::query()
            ->when($pencarian !== '', function ($kueri) use ($pencarian): void {
                $kueri->where(function ($kueri) use ($pencarian): void {
                    $kueri->where('nama', 'like', '%'.$pencarian.'%')
                        ->orWhere('nama_provinsi', 'like', '%'.$pencarian.'%')
                        ->orWhere('kode', 'like', '%'.$pencarian.'%');
                });
            })->orderBy('nama_provinsi')->orderBy('nama')->paginate(25)->appends(['cari' => $pencarian]);
        $jumlahKota = Kota::query()->count();
        $sinkronTerakhir = Kota::query()->max('sinkron_pada');
        $proses = Cache::get(self::KUNCI);

        return view('kota.daftar', compact('daftarKota', 'pencarian', 'jumlahKota', 'sinkronTerakhir', 'proses'));
    }

    public function mulai(): View
    {
        return Cache::lock('sinkron-kota:kunci', 60)->block(5, function (): View {
            $proses = Cache::get(self::KUNCI);
            if (($proses['status'] ?? null) === 'berjalan') {
                return view('kota.progres', compact('proses'));
            }
            $proses = ['id' => (string) Str::uuid(), 'status' => 'berjalan', 'selesai' => 0, 'total' => 0, 'jumlah' => 0, 'pesan' => 'Mengambil daftar provinsi.', 'provinsi' => [], 'kota' => []];

            try {
                $statistik = $this->ambil('stats.json');
                $provinsi = $this->ambil('provinces.json');
                Validator::make(['provinsi' => $provinsi, 'statistik' => $statistik], [
                    'provinsi' => ['required', 'array', 'min:1'],
                    'provinsi.*.id' => ['required', 'string', 'regex:/^\d{2}$/', 'distinct'],
                    'provinsi.*.name' => ['required', 'string', 'max:255'],
                    'statistik.total_provinces' => ['required', 'integer', 'min:1'],
                    'statistik.total_regencies' => ['required', 'integer', 'min:1'],
                ])->validate();
                if (count($provinsi) !== $statistik['total_provinces']) {
                    throw new RuntimeException('Daftar provinsi tidak lengkap.');
                }
                $proses['provinsi'] = $provinsi;
                $proses['total'] = count($provinsi);
                $proses['target'] = $statistik['total_regencies'];
                $proses['pesan'] = 'Daftar provinsi siap. Memulai pengambilan kabupaten/kota.';
            } catch (Throwable $kesalahan) {
                report($kesalahan);
                $proses['status'] = 'gagal';
                $proses['pesan'] = 'Tidak dapat mengambil data API. Periksa koneksi internet lalu coba sinkron kembali. Data lama tetap tersedia.';
            }

            Cache::put(self::KUNCI, $proses, now()->addHour());

            return view('kota.progres', compact('proses'));
        });
    }

    public function lanjutkan(Request $permintaan): View
    {
        $data = $permintaan->validate(['id' => ['required', 'uuid'], 'langkah' => ['required', 'integer', 'min:0']]);

        return Cache::lock('sinkron-kota:kunci', 60)->block(5, function () use ($data): View {
            $proses = Cache::get(self::KUNCI);
            abort_unless($proses && $proses['id'] === $data['id'], 409, 'Proses sinkron sudah kedaluwarsa. Mulai kembali.');
            if ($proses['status'] !== 'berjalan' || $proses['selesai'] !== (int) $data['langkah']) {
                return view('kota.progres', compact('proses'));
            }

            try {
                $provinsi = $proses['provinsi'][$proses['selesai']];
                $daftarKota = $this->ambil('regencies/'.$provinsi['id'].'.json');
                Validator::make(['kota' => $daftarKota], [
                    'kota' => ['required', 'array', 'min:1'],
                    'kota.*.id' => ['required', 'string', 'regex:/^\d{2}\.\d{2}$/', 'distinct'],
                    'kota.*.name' => ['required', 'string', 'max:255'],
                ])->validate();
                foreach ($daftarKota as $kota) {
                    if (! str_starts_with($kota['id'], $provinsi['id'].'.') || isset($proses['kota'][$kota['id']])) {
                        throw new RuntimeException('Kode wilayah tidak sesuai atau berulang.');
                    }
                    $proses['kota'][$kota['id']] = ['kode' => $kota['id'], 'nama' => $kota['name'], 'kode_provinsi' => $provinsi['id'], 'nama_provinsi' => $provinsi['name']];
                }
                $proses['selesai']++;
                $proses['jumlah'] = count($proses['kota']);
                $proses['pesan'] = $provinsi['name'].' selesai: '.count($daftarKota).' kabupaten/kota.';

                if ($proses['selesai'] === $proses['total']) {
                    if ($proses['jumlah'] !== $proses['target']) {
                        throw new RuntimeException('Jumlah wilayah tidak sesuai statistik sumber.');
                    }
                    DB::transaction(function () use ($proses): void {
                        $waktu = now();
                        foreach (array_chunk(array_values($proses['kota']), 200) as $kelompok) {
                            $baris = array_map(fn (array $kota): array => [...$kota, 'sinkron_pada' => $waktu, 'created_at' => $waktu, 'updated_at' => $waktu], $kelompok);
                            Kota::query()->upsert($baris, ['kode'], ['nama', 'kode_provinsi', 'nama_provinsi', 'sinkron_pada', 'updated_at']);
                        }
                    });
                    $proses['status'] = 'berhasil';
                    $proses['pesan'] = 'Sinkron selesai. '.$proses['jumlah'].' kabupaten/kota dari '.$proses['total'].' provinsi berhasil disimpan.';
                    $proses['kota'] = [];
                    $proses['provinsi'] = [];
                }
            } catch (Throwable $kesalahan) {
                report($kesalahan);
                $proses['status'] = 'gagal';
                $proses['pesan'] = 'Sinkron gagal saat mengambil atau menyimpan wilayah. Data lama tidak berubah. Silakan coba kembali.';
                $proses['kota'] = [];
            }
            Cache::put(self::KUNCI, $proses, now()->addHour());

            return view('kota.progres', compact('proses'));
        });
    }

    /** @return array<string|int, mixed> */
    private function ambil(string $jalur): array
    {
        $data = Http::acceptJson()->connectTimeout(3)->timeout(6)->retry(2, 300)
            ->get(rtrim(config('services.wilayah.url'), '/').'/'.$jalur)->throw()->json('data');
        if (! is_array($data) || $data === []) {
            throw new RuntimeException('Format data API tidak valid.');
        }

        return $data;
    }
}

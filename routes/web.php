<?php

use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\AutentikasiController;
use App\Http\Controllers\JabatanController;
use App\Http\Controllers\JadwalKerjaController;
use App\Http\Controllers\KantorController;
use App\Http\Controllers\KetidakhadiranController;
use App\Http\Controllers\KotaController;
use App\Http\Controllers\LaporanHrController;
use App\Http\Controllers\LemburController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\PenempatanPegawaiController;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ShiftPegawaiController;
use App\Http\Controllers\StatusPegawaiController;
use App\Http\Controllers\UnitOrganisasiController;
use App\Http\Middleware\PastikanPengelola;
use App\Http\Middleware\PastikanPenggunaAktif;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('beranda'));
Route::prefix('administrator')->group(function (): void {
    Route::get('/', fn () => redirect()->route('beranda'));
    Route::middleware('guest')->group(function (): void {
        Route::get('/login', [AutentikasiController::class, 'formulir'])->name('login');
        Route::post('/login', [AutentikasiController::class, 'masuk'])->name('login.masuk');
    });
    Route::post('/logout', [AutentikasiController::class, 'keluar'])->middleware('auth')->name('logout');

    Route::middleware(['auth', PastikanPenggunaAktif::class])->group(function (): void {
        Route::view('/beranda', 'beranda')->name('beranda');

        Route::middleware(PastikanPengelola::class)->group(function (): void {
            Route::get('/laporan-hr', [LaporanHrController::class, 'daftar'])->name('laporan-hr.daftar');
            Route::prefix('jadwal-kerja')->name('jadwal-kerja.')->controller(JadwalKerjaController::class)->group(function (): void {
                Route::get('/', 'daftar')->name('daftar');
                Route::post('/', 'simpan')->name('simpan');
                Route::put('/{jadwal}', 'perbarui')->whereUuid('jadwal')->name('perbarui');
                Route::delete('/{jadwal}', 'hapus')->whereUuid('jadwal')->name('hapus');
            });
            Route::get('/absensi', [AbsensiController::class, 'daftar'])->name('absensi.daftar');
            Route::post('/absensi/{jadwal}', [AbsensiController::class, 'simpan'])->whereUuid('jadwal')->name('absensi.simpan');
            Route::prefix('cuti-izin-sakit')->name('ketidakhadiran.')->controller(KetidakhadiranController::class)->group(function (): void {
                Route::get('/', 'daftar')->name('daftar');
                Route::post('/', 'simpan')->name('simpan');
                Route::post('/{pengajuan}/keputusan', 'keputusan')->whereUuid('pengajuan')->name('keputusan');
                Route::get('/{pengajuan}/lampiran', 'lampiran')->whereUuid('pengajuan')->name('lampiran');
            });
            Route::prefix('lembur')->name('lembur.')->controller(LemburController::class)->group(function (): void {
                Route::get('/', 'daftar')->name('daftar');
                Route::post('/', 'simpan')->name('simpan');
                Route::post('/{lembur}/keputusan', 'keputusan')->whereUuid('lembur')->name('keputusan');
            });
            Route::prefix('penempatan-pegawai')->name('penempatan-pegawai.')->controller(PenempatanPegawaiController::class)->group(function (): void {
                Route::get('/', 'daftar')->name('daftar');
                Route::post('/', 'simpan')->name('simpan');
                Route::get('/{penempatan}', 'tampilkan')->whereUuid('penempatan')->name('tampilkan');
            });
            Route::prefix('pegawai')->name('pegawai.')->controller(PegawaiController::class)->group(function (): void {
                Route::get('/', 'daftar')->name('daftar');
                Route::get('/tambah', 'tambah')->name('tambah');
                Route::post('/', 'simpan')->name('simpan');
                Route::get('/{pegawai}', 'tampilkan')->whereUuid('pegawai')->name('tampilkan');
                Route::get('/{pegawai}/ubah', 'ubah')->whereUuid('pegawai')->name('ubah');
                Route::match(['put', 'patch'], '/{pegawai}', 'perbarui')->whereUuid('pegawai')->name('perbarui');
                Route::delete('/{pegawai}', 'hapus')->whereUuid('pegawai')->name('hapus');
            });
            Route::prefix('shift-pegawai')->name('shift-pegawai.')->controller(ShiftPegawaiController::class)->group(function (): void {
                Route::get('/', 'daftar')->name('daftar');
                Route::get('/tambah', 'tambah')->name('tambah');
                Route::post('/', 'simpan')->name('simpan');
                Route::get('/{shift}/ubah', 'ubah')->whereUuid('shift')->name('ubah');
                Route::match(['put', 'patch'], '/{shift}', 'perbarui')->whereUuid('shift')->name('perbarui');
                Route::delete('/{shift}', 'hapus')->whereUuid('shift')->name('hapus');
            });
            Route::prefix('status-pegawai')->name('status-pegawai.')->controller(StatusPegawaiController::class)->group(function (): void {
                Route::get('/', 'daftar')->name('daftar');
                Route::get('/tambah', 'tambah')->name('tambah');
                Route::post('/', 'simpan')->name('simpan');
                Route::get('/{statusPegawai}/ubah', 'ubah')->whereUuid('statusPegawai')->name('ubah');
                Route::match(['put', 'patch'], '/{statusPegawai}', 'perbarui')->whereUuid('statusPegawai')->name('perbarui');
                Route::delete('/{statusPegawai}', 'hapus')->whereUuid('statusPegawai')->name('hapus');
            });
            Route::prefix('jabatan')->name('jabatan.')->controller(JabatanController::class)->group(function (): void {
                Route::get('/', 'daftar')->name('daftar');
                Route::get('/tambah', 'tambah')->name('tambah');
                Route::post('/', 'simpan')->name('simpan');
                Route::get('/{jabatan}', 'tampilkan')->whereUuid('jabatan')->name('tampilkan');
                Route::get('/{jabatan}/ubah', 'ubah')->whereUuid('jabatan')->name('ubah');
                Route::match(['put', 'patch'], '/{jabatan}', 'perbarui')->whereUuid('jabatan')->name('perbarui');
                Route::delete('/{jabatan}', 'hapus')->whereUuid('jabatan')->name('hapus');
            });
            Route::prefix('unit-organisasi')->name('unit-organisasi.')->controller(UnitOrganisasiController::class)->group(function (): void {
                Route::get('/', 'daftar')->name('daftar');
                Route::get('/tambah', 'tambah')->name('tambah');
                Route::post('/', 'simpan')->name('simpan');
                Route::get('/{unit}', 'tampilkan')->whereUuid('unit')->name('tampilkan');
                Route::get('/{unit}/ubah', 'ubah')->whereUuid('unit')->name('ubah');
                Route::match(['put', 'patch'], '/{unit}', 'perbarui')->whereUuid('unit')->name('perbarui');
                Route::delete('/{unit}', 'hapus')->whereUuid('unit')->name('hapus');
            });
            Route::prefix('kantor')->name('kantor.')->controller(KantorController::class)->group(function (): void {
                Route::get('/', 'daftar')->name('daftar');
                Route::get('/tambah', 'tambah')->name('tambah');
                Route::post('/', 'simpan')->name('simpan');
                Route::get('/{kantor}', 'tampilkan')->whereUuid('kantor')->name('tampilkan');
                Route::get('/{kantor}/ubah', 'ubah')->whereUuid('kantor')->name('ubah');
                Route::match(['put', 'patch'], '/{kantor}', 'perbarui')->whereUuid('kantor')->name('perbarui');
                Route::delete('/{kantor}', 'hapus')->whereUuid('kantor')->name('hapus');
            });
            Route::prefix('kota')->name('kota.')->controller(KotaController::class)->group(function (): void {
                Route::get('/', 'daftar')->name('daftar');
                Route::post('/sinkron', 'mulai')->name('sinkron');
                Route::post('/sinkron/lanjut', 'lanjutkan')->name('lanjutkan');
            });
            Route::prefix('role')->name('role.')->controller(RoleController::class)->group(function (): void {
                Route::get('/', 'daftar')->name('daftar');
                Route::get('/tambah', 'tambah')->name('tambah');
                Route::post('/', 'simpan')->name('simpan');
                Route::get('/{role}', 'tampilkan')->whereUuid('role')->name('tampilkan');
                Route::get('/{role}/ubah', 'ubah')->whereUuid('role')->name('ubah');
                Route::match(['put', 'patch'], '/{role}', 'perbarui')->whereUuid('role')->name('perbarui');
                Route::delete('/{role}', 'hapus')->whereUuid('role')->name('hapus');
            });
            Route::prefix('users')->name('pengguna.')->controller(PenggunaController::class)->group(function (): void {
                Route::get('/', 'daftar')->name('daftar');
                Route::get('/tambah', 'tambah')->name('tambah');
                Route::post('/', 'simpan')->name('simpan');
                Route::get('/{pengguna}', 'tampilkan')->whereNumber('pengguna')->name('tampilkan');
                Route::get('/{pengguna}/ubah', 'ubah')->whereNumber('pengguna')->name('ubah');
                Route::match(['put', 'patch'], '/{pengguna}', 'perbarui')->whereNumber('pengguna')->name('perbarui');
                Route::delete('/{pengguna}', 'hapus')->whereNumber('pengguna')->name('hapus');
            });
        });
    });
});

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal_kerja', function (Blueprint $tabel): void {
            $tabel->uuid('id')->primary();
            $tabel->foreignUuid('pegawai_id')->constrained('pegawai')->restrictOnDelete();
            $tabel->date('tanggal');
            $tabel->boolean('libur')->default(false);
            $tabel->string('nama_shift')->nullable();
            $tabel->dateTime('mulai')->nullable();
            $tabel->dateTime('selesai')->nullable();
            $tabel->boolean('wajib_qr')->default(false);
            $tabel->string('nama_kantor');
            $tabel->text('keterangan')->nullable();
            $tabel->timestamps();
            $tabel->unique(['pegawai_id', 'tanggal']);
            $tabel->index('tanggal');
        });
        Schema::create('absensi', function (Blueprint $tabel): void {
            $tabel->uuid('id')->primary();
            $tabel->foreignUuid('jadwal_kerja_id')->unique()->constrained('jadwal_kerja')->restrictOnDelete();
            $tabel->dateTime('masuk');
            $tabel->dateTime('pulang')->nullable();
            $tabel->unsignedInteger('terlambat')->default(0);
            $tabel->unsignedInteger('pulang_awal')->default(0);
            $tabel->unsignedInteger('menit_kerja')->default(0);
            $tabel->unsignedInteger('versi')->default(1);
            $tabel->text('keterangan');
            $tabel->foreignId('pencatat_id')->nullable()->constrained('users')->nullOnDelete();
            $tabel->timestamps();
        });
        Schema::create('riwayat_absensi', function (Blueprint $tabel): void {
            $tabel->uuid('id')->primary();
            $tabel->foreignUuid('absensi_id')->constrained('absensi')->restrictOnDelete();
            $tabel->dateTime('masuk_lama')->nullable();
            $tabel->dateTime('pulang_lama')->nullable();
            $tabel->dateTime('masuk_baru');
            $tabel->dateTime('pulang_baru')->nullable();
            $tabel->text('alasan');
            $tabel->string('nama_pencatat');
            $tabel->timestamps();
        });
        Schema::create('ketidakhadiran', function (Blueprint $tabel): void {
            $tabel->uuid('id')->primary();
            $tabel->foreignUuid('pegawai_id')->constrained('pegawai')->restrictOnDelete();
            $tabel->string('jenis', 20);
            $tabel->date('tanggal_mulai');
            $tabel->date('tanggal_selesai');
            $tabel->unsignedSmallInteger('jumlah_hari');
            $tabel->string('status', 20)->default('menunggu');
            $tabel->text('alasan');
            $tabel->string('lampiran')->nullable();
            $tabel->foreignId('pencatat_id')->nullable()->constrained('users')->nullOnDelete();
            $tabel->foreignId('pemutus_id')->nullable()->constrained('users')->nullOnDelete();
            $tabel->string('nama_pencatat');
            $tabel->string('nama_pemutus')->nullable();
            $tabel->text('catatan_keputusan')->nullable();
            $tabel->timestamp('diputus_pada')->nullable();
            $tabel->timestamps();
            $tabel->index(['pegawai_id', 'tanggal_mulai', 'tanggal_selesai'], 'ketidakhadiran_periode');
        });
        Schema::create('lembur', function (Blueprint $tabel): void {
            $tabel->uuid('id')->primary();
            $tabel->foreignUuid('jadwal_kerja_id')->constrained('jadwal_kerja')->restrictOnDelete();
            $tabel->dateTime('mulai');
            $tabel->dateTime('selesai');
            $tabel->unsignedSmallInteger('durasi');
            $tabel->string('status', 20)->default('menunggu');
            $tabel->text('alasan');
            $tabel->foreignId('pencatat_id')->nullable()->constrained('users')->nullOnDelete();
            $tabel->foreignId('pemutus_id')->nullable()->constrained('users')->nullOnDelete();
            $tabel->string('nama_pencatat');
            $tabel->string('nama_pemutus')->nullable();
            $tabel->text('catatan_keputusan')->nullable();
            $tabel->timestamp('diputus_pada')->nullable();
            $tabel->timestamps();
        });
        Schema::create('riwayat_keputusan', function (Blueprint $tabel): void {
            $tabel->uuid('id')->primary();
            $tabel->foreignUuid('ketidakhadiran_id')->nullable()->constrained('ketidakhadiran')->restrictOnDelete();
            $tabel->foreignUuid('lembur_id')->nullable()->constrained('lembur')->restrictOnDelete();
            $tabel->string('status_lama', 20);
            $tabel->string('status_baru', 20);
            $tabel->string('nama_pemutus');
            $tabel->foreignId('pemutus_id')->nullable()->constrained('users')->nullOnDelete();
            $tabel->text('catatan');
            $tabel->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['riwayat_keputusan', 'lembur', 'ketidakhadiran', 'riwayat_absensi', 'absensi', 'jadwal_kerja'] as $nama) {
            Schema::dropIfExists($nama);
        }
    }
};

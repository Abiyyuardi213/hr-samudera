<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penempatan_pegawai', function (Blueprint $tabel): void {
            $tabel->uuid('id')->primary();
            $tabel->foreignUuid('pegawai_id')->constrained('pegawai')->restrictOnDelete();
            $tabel->foreignUuid('jabatan_id')->constrained('jabatan')->restrictOnDelete();
            $tabel->foreignUuid('divisi_id')->nullable()->constrained('unit_organisasi')->restrictOnDelete();
            $tabel->foreignUuid('kantor_id')->constrained('kantor')->restrictOnDelete();
            $tabel->foreignUuid('status_pegawai_id')->constrained('status_pegawai')->restrictOnDelete();
            $tabel->foreignUuid('shift_pegawai_id')->constrained('shift_pegawai')->restrictOnDelete();
            $tabel->string('nama_jabatan');
            $tabel->string('nama_unit');
            $tabel->string('nama_divisi')->nullable();
            $tabel->string('nama_kantor');
            $tabel->string('nama_status_pegawai');
            $tabel->string('nama_shift');
            $tabel->time('jam_masuk');
            $tabel->time('jam_pulang');
            $tabel->unsignedSmallInteger('durasi_shift');
            $tabel->boolean('wajib_qr');
            $tabel->string('jenis', 30);
            $tabel->date('tanggal_mulai');
            $tabel->date('tanggal_selesai')->nullable();
            $tabel->string('nomor_sk', 100)->nullable();
            $tabel->text('keterangan')->nullable();
            $tabel->foreignId('pencatat_id')->nullable()->constrained('users')->nullOnDelete();
            $tabel->string('nama_pencatat')->nullable();
            $tabel->timestamps();
            $tabel->unique(['pegawai_id', 'tanggal_mulai']);
            $tabel->index(['pegawai_id', 'tanggal_selesai']);
        });

        DB::table('pegawai as p')
            ->join('jabatan as j', 'j.id', '=', 'p.jabatan_id')
            ->join('unit_organisasi as u', 'u.id', '=', 'j.unit_organisasi_id')
            ->leftJoin('unit_organisasi as d', 'd.id', '=', 'p.divisi_id')
            ->join('kantor as k', 'k.id', '=', 'p.kantor_id')
            ->join('status_pegawai as s', 's.id', '=', 'p.status_pegawai_id')
            ->join('shift_pegawai as sh', 'sh.id', '=', 'p.shift_pegawai_id')
            ->select('p.id', 'p.jabatan_id', 'p.divisi_id', 'p.kantor_id', 'p.status_pegawai_id', 'p.shift_pegawai_id', 'p.tanggal_masuk', 'j.nama as nama_jabatan', 'u.nama as nama_unit', 'd.nama as nama_divisi', 'k.nama_kantor', 's.nama as nama_status_pegawai', 'sh.nama_shift', 'sh.jam_masuk', 'sh.jam_pulang', 'sh.durasi as durasi_shift', 'sh.wajib_qr')
            ->chunkById(100, function ($daftarPegawai): void {
                $waktu = now();
                $baris = [];
                foreach ($daftarPegawai as $pegawai) {
                    $data = (array) $pegawai;
                    unset($data['id'], $data['tanggal_masuk']);
                    $baris[] = [...$data, 'id' => (string) Str::uuid(), 'pegawai_id' => $pegawai->id, 'jenis' => 'awal', 'tanggal_mulai' => $pegawai->tanggal_masuk, 'keterangan' => 'Penempatan awal dari data pegawai sebelum modul riwayat tersedia.', 'created_at' => $waktu, 'updated_at' => $waktu];
                }
                DB::table('penempatan_pegawai')->insert($baris);
            }, 'p.id', 'id');
    }

    public function down(): void
    {
        Schema::dropIfExists('penempatan_pegawai');
    }
};

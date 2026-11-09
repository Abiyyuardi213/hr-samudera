<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pegawai', function (Blueprint $tabel): void {
            $tabel->uuid('id')->primary();
            $tabel->string('nama_lengkap');
            $tabel->string('nip', 50)->unique();
            $tabel->foreignUuid('jabatan_id')->constrained('jabatan')->restrictOnDelete();
            $tabel->foreignUuid('divisi_id')->nullable()->constrained('unit_organisasi')->restrictOnDelete();
            $tabel->foreignUuid('kantor_id')->constrained('kantor')->restrictOnDelete();
            $tabel->foreignUuid('status_pegawai_id')->constrained('status_pegawai')->restrictOnDelete();
            $tabel->string('nik', 16)->unique();
            $tabel->string('tempat_lahir');
            $tabel->date('tanggal_lahir');
            $tabel->string('jenis_kelamin', 20);
            $tabel->text('alamat_domisili');
            $tabel->date('tanggal_masuk');
            $tabel->foreignUuid('shift_pegawai_id')->constrained('shift_pegawai')->restrictOnDelete();
            $tabel->unsignedSmallInteger('sisa_cuti')->default(0);
            $tabel->string('nomor_telepon', 30);
            $tabel->string('email')->unique();
            $tabel->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pegawai');
    }
};

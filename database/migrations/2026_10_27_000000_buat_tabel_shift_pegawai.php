<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shift_pegawai', function (Blueprint $tabel): void {
            $tabel->uuid('id')->primary();
            $tabel->string('nama_shift');
            $tabel->time('jam_masuk');
            $tabel->time('jam_pulang');
            $tabel->unsignedSmallInteger('durasi');
            $tabel->boolean('wajib_qr')->default(false);
            $tabel->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_pegawai');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('status_pegawai', function (Blueprint $tabel): void {
            $tabel->uuid('id')->primary();
            $tabel->string('kode', 70)->unique();
            $tabel->string('nama');
            $tabel->text('deskripsi')->nullable();
            $tabel->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('status_pegawai');
    }
};

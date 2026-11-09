<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kantor', function (Blueprint $tabel): void {
            $tabel->uuid('id')->primary();
            $tabel->string('kode_kantor', 70)->unique();
            $tabel->string('nama_kantor');
            $tabel->foreignId('kota_id')->constrained('kota')->restrictOnDelete();
            $tabel->text('alamat_lengkap');
            $tabel->string('nomor_telepon', 30);
            $tabel->string('email');
            $tabel->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kantor');
    }
};

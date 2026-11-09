<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jabatan', function (Blueprint $tabel): void {
            $tabel->uuid('id')->primary();
            $tabel->string('nama');
            $tabel->foreignUuid('unit_organisasi_id')->constrained('unit_organisasi')->restrictOnDelete();
            $tabel->foreignUuid('atasan_id')->nullable()->constrained('jabatan')->restrictOnDelete();
            $tabel->text('deskripsi')->nullable();
            $tabel->boolean('status')->default(true);
            $tabel->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jabatan');
    }
};

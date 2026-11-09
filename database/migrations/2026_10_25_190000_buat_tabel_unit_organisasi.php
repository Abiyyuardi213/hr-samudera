<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unit_organisasi', function (Blueprint $tabel): void {
            $tabel->uuid('id')->primary();
            $tabel->string('nama');
            $tabel->string('jenis_unit', 40);
            $tabel->foreignUuid('induk_id')->nullable()->constrained('unit_organisasi')->restrictOnDelete();
            $tabel->text('deskripsi')->nullable();
            $tabel->boolean('status')->default(true);
            $tabel->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_organisasi');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $tabel): void {
            $tabel->foreignUuid('role_id')->nullable()->constrained('role')->restrictOnDelete();
            $tabel->boolean('status')->default(true);
            $tabel->string('foto_profil')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $tabel): void {
            $tabel->dropForeign(['role_id']);
            $tabel->dropColumn(['role_id', 'status', 'foto_profil']);
        });
    }
};

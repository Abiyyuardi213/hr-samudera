<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['jadwal_kerja_id', 'masuk', 'pulang', 'terlambat', 'pulang_awal', 'menit_kerja', 'keterangan', 'pencatat_id', 'versi'])]
class Absensi extends Model
{
    use HasUuids;

    protected $table = 'absensi';

    public function jadwalKerja(): BelongsTo
    {
        return $this->belongsTo(JadwalKerja::class);
    }

    public function riwayat(): HasMany
    {
        return $this->hasMany(RiwayatAbsensi::class)->latest()->orderByDesc('id');
    }

    protected function casts(): array
    {
        return ['masuk' => 'datetime', 'pulang' => 'datetime', 'terlambat' => 'integer', 'pulang_awal' => 'integer', 'menit_kerja' => 'integer'];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['jadwal_kerja_id', 'mulai', 'selesai', 'durasi', 'status', 'alasan', 'pencatat_id', 'nama_pencatat', 'pemutus_id', 'nama_pemutus', 'catatan_keputusan', 'diputus_pada'])]
class Lembur extends Model
{
    use HasUuids;

    protected $table = 'lembur';

    public function jadwalKerja(): BelongsTo
    {
        return $this->belongsTo(JadwalKerja::class);
    }

    public function riwayatKeputusan(): HasMany
    {
        return $this->hasMany(RiwayatKeputusan::class)->latest()->orderByDesc('id');
    }

    protected function casts(): array
    {
        return ['mulai' => 'datetime', 'selesai' => 'datetime', 'durasi' => 'integer', 'diputus_pada' => 'datetime'];
    }
}

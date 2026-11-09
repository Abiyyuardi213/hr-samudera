<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nama', 'unit_organisasi_id', 'atasan_id', 'deskripsi', 'status'])]
class Jabatan extends Model
{
    use HasUuids;

    protected $table = 'jabatan';

    public function riwayatPenempatan(): HasMany
    {
        return $this->hasMany(PenempatanPegawai::class, 'jabatan_id');
    }

    public function pegawai(): HasMany
    {
        return $this->hasMany(Pegawai::class, 'jabatan_id');
    }

    public function unitOrganisasi(): BelongsTo
    {
        return $this->belongsTo(UnitOrganisasi::class);
    }

    public function atasan(): BelongsTo
    {
        return $this->belongsTo(self::class, 'atasan_id');
    }

    public function bawahan(): HasMany
    {
        return $this->hasMany(self::class, 'atasan_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['status' => 'boolean'];
    }
}

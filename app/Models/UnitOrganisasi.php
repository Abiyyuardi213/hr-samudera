<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nama', 'jenis_unit', 'induk_id', 'deskripsi', 'status'])]
class UnitOrganisasi extends Model
{
    use HasUuids;

    public const JENIS_UNIT = [
        'direksi' => 'Direksi',
        'direktorat' => 'Direktorat',
        'divisi' => 'Divisi',
        'departemen' => 'Departemen',
        'bagian' => 'Bagian',
        'ceo_office' => 'CEO Office',
        'internal_audit' => 'Internal Audit',
        'unit_bisnis' => 'Unit Bisnis',
        'yayasan' => 'Yayasan',
        'lainnya' => 'Lainnya',
    ];

    protected $table = 'unit_organisasi';

    public function riwayatPenempatan(): HasMany
    {
        return $this->hasMany(PenempatanPegawai::class, 'divisi_id');
    }

    public function pegawai(): HasMany
    {
        return $this->hasMany(Pegawai::class, 'divisi_id');
    }

    public function jabatan(): HasMany
    {
        return $this->hasMany(Jabatan::class);
    }

    public function induk(): BelongsTo
    {
        return $this->belongsTo(self::class, 'induk_id');
    }

    public function anak(): HasMany
    {
        return $this->hasMany(self::class, 'induk_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['status' => 'boolean'];
    }
}

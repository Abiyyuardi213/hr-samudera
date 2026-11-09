<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['nama', 'deskripsi'])]
class StatusPegawai extends Model
{
    use HasUuids;

    protected $table = 'status_pegawai';

    public function riwayatPenempatan(): HasMany
    {
        return $this->hasMany(PenempatanPegawai::class, 'status_pegawai_id');
    }

    public function pegawai(): HasMany
    {
        return $this->hasMany(Pegawai::class, 'status_pegawai_id');
    }

    protected static function booted(): void
    {
        static::creating(function (StatusPegawai $statusPegawai): void {
            $namaKode = Str::upper(substr(Str::slug($statusPegawai->nama), 0, 50)) ?: 'STATUS';
            do {
                $kode = 'STP-'.$namaKode.'-'.Str::upper(Str::random(6));
            } while (static::query()->where('kode', $kode)->exists());
            $statusPegawai->kode = $kode;
        });
    }
}

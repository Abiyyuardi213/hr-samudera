<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['nama_kantor', 'kota_id', 'alamat_lengkap', 'nomor_telepon', 'email'])]
class Kantor extends Model
{
    use HasUuids;

    protected $table = 'kantor';

    public function riwayatPenempatan(): HasMany
    {
        return $this->hasMany(PenempatanPegawai::class, 'kantor_id');
    }

    public function pegawai(): HasMany
    {
        return $this->hasMany(Pegawai::class, 'kantor_id');
    }

    protected static function booted(): void
    {
        static::creating(function (Kantor $kantor): void {
            $namaKode = Str::upper(substr(Str::slug($kantor->nama_kantor), 0, 50)) ?: 'KANTOR';
            do {
                $kodeKantor = 'KTR-'.$namaKode.'-'.Str::upper(Str::random(6));
            } while (static::query()->where('kode_kantor', $kodeKantor)->exists());

            $kantor->kode_kantor = $kodeKantor;
        });
    }

    public function kota(): BelongsTo
    {
        return $this->belongsTo(Kota::class);
    }
}

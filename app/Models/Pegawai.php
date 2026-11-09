<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nama_lengkap', 'nip', 'jabatan_id', 'divisi_id', 'kantor_id', 'status_pegawai_id', 'nik', 'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin', 'alamat_domisili', 'tanggal_masuk', 'shift_pegawai_id', 'sisa_cuti', 'nomor_telepon', 'email'])]
class Pegawai extends Model
{
    use HasUuids;

    protected $table = 'pegawai';

    public function jadwalKerja(): HasMany
    {
        return $this->hasMany(JadwalKerja::class);
    }

    public function ketidakhadiran(): HasMany
    {
        return $this->hasMany(Ketidakhadiran::class);
    }

    public function riwayatPenempatan(): HasMany
    {
        return $this->hasMany(PenempatanPegawai::class);
    }

    public function jabatan(): BelongsTo
    {
        return $this->belongsTo(Jabatan::class);
    }

    public function divisi(): BelongsTo
    {
        return $this->belongsTo(UnitOrganisasi::class, 'divisi_id');
    }

    public function kantor(): BelongsTo
    {
        return $this->belongsTo(Kantor::class);
    }

    public function statusPegawai(): BelongsTo
    {
        return $this->belongsTo(StatusPegawai::class);
    }

    public function shiftPegawai(): BelongsTo
    {
        return $this->belongsTo(ShiftPegawai::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['tanggal_lahir' => 'date', 'tanggal_masuk' => 'date', 'sisa_cuti' => 'integer'];
    }
}

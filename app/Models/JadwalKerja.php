<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

#[Fillable(['pegawai_id', 'tanggal', 'libur', 'nama_shift', 'mulai', 'selesai', 'wajib_qr', 'nama_kantor', 'keterangan'])]
class JadwalKerja extends Model
{
    use HasUuids;

    protected $table = 'jadwal_kerja';

    protected function tanggal(): Attribute
    {
        return Attribute::make(set: fn (string $nilai): string => substr($nilai, 0, 10));
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class);
    }

    public function absensi(): HasOne
    {
        return $this->hasOne(Absensi::class);
    }

    public function lembur(): HasMany
    {
        return $this->hasMany(Lembur::class);
    }

    public function pastikanDapatDiubah(): void
    {
        if ($this->absensi()->exists() || $this->lembur()->whereIn('status', ['menunggu', 'disetujui'])->exists() || Ketidakhadiran::aktifPada($this->pegawai_id, $this->tanggal->toDateString())->exists()) {
            throw ValidationException::withMessages(['jadwal' => 'Jadwal telah dipakai absensi atau pengajuan aktif. Batalkan pengajuan terlebih dahulu; jadwal dengan absensi tidak dapat diubah.']);
        }
    }

    public static function waktu(string $nilai): Carbon
    {
        return Carbon::parse($nilai, 'Asia/Jakarta');
    }

    protected function casts(): array
    {
        return ['tanggal' => 'date', 'libur' => 'boolean', 'wajib_qr' => 'boolean', 'mulai' => 'datetime', 'selesai' => 'datetime'];
    }
}

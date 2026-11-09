<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use InvalidArgumentException;

#[Fillable(['nama_shift', 'jam_masuk', 'jam_pulang', 'wajib_qr'])]
class ShiftPegawai extends Model
{
    use HasUuids;

    protected $table = 'shift_pegawai';

    public function riwayatPenempatan(): HasMany
    {
        return $this->hasMany(PenempatanPegawai::class, 'shift_pegawai_id');
    }

    public function pegawai(): HasMany
    {
        return $this->hasMany(Pegawai::class, 'shift_pegawai_id');
    }

    protected static function booted(): void
    {
        static::saving(function (ShiftPegawai $shift): void {
            $shift->durasi = self::hitungDurasi($shift->jam_masuk, $shift->jam_pulang);
        });
    }

    public static function hitungDurasi(string $jamMasuk, string $jamPulang): int
    {
        foreach ([$jamMasuk, $jamPulang] as $jam) {
            if (! preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d(?::00)?$/', $jam)) {
                throw new InvalidArgumentException('Jam harus menggunakan format HH:MM.');
            }
        }
        $menitMasuk = (int) substr($jamMasuk, 0, 2) * 60 + (int) substr($jamMasuk, 3, 2);
        $menitPulang = (int) substr($jamPulang, 0, 2) * 60 + (int) substr($jamPulang, 3, 2);
        $durasi = ($menitPulang - $menitMasuk + 1440) % 1440;
        if ($durasi === 0) {
            throw new InvalidArgumentException('Jam masuk dan jam pulang tidak boleh sama.');
        }

        return $durasi;
    }

    public function durasiTerbaca(): string
    {
        $jam = intdiv($this->durasi, 60);
        $menit = $this->durasi % 60;

        return $jam.' jam'.($menit > 0 ? ' '.$menit.' menit' : '');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['wajib_qr' => 'boolean', 'durasi' => 'integer'];
    }
}

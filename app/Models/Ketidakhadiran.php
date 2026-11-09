<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

#[Fillable(['pegawai_id', 'jenis', 'tanggal_mulai', 'tanggal_selesai', 'jumlah_hari', 'status', 'alasan', 'lampiran', 'pencatat_id', 'nama_pencatat', 'pemutus_id', 'nama_pemutus', 'catatan_keputusan', 'diputus_pada'])]
class Ketidakhadiran extends Model
{
    use HasUuids;

    protected $table = 'ketidakhadiran';

    protected function tanggalMulai(): Attribute
    {
        return Attribute::make(set: fn (string $nilai): string => substr($nilai, 0, 10));
    }

    protected function tanggalSelesai(): Attribute
    {
        return Attribute::make(set: fn (string $nilai): string => substr($nilai, 0, 10));
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class);
    }

    public function riwayatKeputusan(): HasMany
    {
        return $this->hasMany(RiwayatKeputusan::class)->latest()->orderByDesc('id');
    }

    public static function aktifPada(string $pegawaiId, string $tanggal): Builder
    {
        return self::query()->where('pegawai_id', $pegawaiId)->whereIn('status', ['menunggu', 'disetujui'])->where('tanggal_mulai', '<=', $tanggal)->where('tanggal_selesai', '>=', $tanggal);
    }

    public function putuskan(string $keputusan, string $catatan, User $pengguna): void
    {
        DB::transaction(function () use ($keputusan, $catatan, $pengguna): void {
            $pegawai = Pegawai::query()->lockForUpdate()->findOrFail($this->pegawai_id);
            $pengajuan = self::query()->lockForUpdate()->findOrFail($this->id);
            if (($keputusan === 'dibatalkan' && ! in_array($pengajuan->status, ['menunggu', 'disetujui'], true)) || ($keputusan !== 'dibatalkan' && $pengajuan->status !== 'menunggu')) {
                throw ValidationException::withMessages(['status' => 'Status pengajuan sudah berubah. Muat ulang halaman.']);
            }
            if ($keputusan === 'disetujui' && $pengajuan->jenis === 'cuti') {
                if ($pegawai->sisa_cuti < $pengajuan->jumlah_hari) {
                    throw ValidationException::withMessages(['saldo' => 'Saldo cuti tidak mencukupi.']);
                }
                $pegawai->decrement('sisa_cuti', $pengajuan->jumlah_hari);
            }
            if ($keputusan === 'dibatalkan' && $pengajuan->status === 'disetujui' && $pengajuan->jenis === 'cuti') {
                $pegawai->increment('sisa_cuti', $pengajuan->jumlah_hari);
            }
            RiwayatKeputusan::query()->create(['ketidakhadiran_id' => $pengajuan->id, 'status_lama' => $pengajuan->status, 'status_baru' => $keputusan, 'pemutus_id' => $pengguna->id, 'nama_pemutus' => $pengguna->name, 'catatan' => $catatan]);
            $pengajuan->update(['status' => $keputusan, 'pemutus_id' => $pengguna->id, 'nama_pemutus' => $pengguna->name, 'catatan_keputusan' => $catatan, 'diputus_pada' => now()]);
        }, 3);
    }

    protected function casts(): array
    {
        return ['tanggal_mulai' => 'date', 'tanggal_selesai' => 'date', 'jumlah_hari' => 'integer', 'diputus_pada' => 'datetime'];
    }
}

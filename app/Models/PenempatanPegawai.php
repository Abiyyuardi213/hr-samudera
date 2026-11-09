<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

#[Fillable(['pegawai_id', 'jabatan_id', 'divisi_id', 'kantor_id', 'status_pegawai_id', 'shift_pegawai_id', 'nama_jabatan', 'nama_unit', 'nama_divisi', 'nama_kantor', 'nama_status_pegawai', 'nama_shift', 'jam_masuk', 'jam_pulang', 'durasi_shift', 'wajib_qr', 'jenis', 'tanggal_mulai', 'tanggal_selesai', 'nomor_sk', 'keterangan', 'pencatat_id', 'nama_pencatat'])]
class PenempatanPegawai extends Model
{
    use HasUuids;

    public const KOLOM_PENEMPATAN = ['jabatan_id', 'divisi_id', 'kantor_id', 'status_pegawai_id', 'shift_pegawai_id'];

    public const JENIS = [
        'awal' => 'Penempatan Awal', 'mutasi' => 'Mutasi', 'promosi' => 'Promosi',
        'demosi' => 'Demosi', 'rotasi' => 'Rotasi', 'perubahan_shift' => 'Perubahan Shift',
        'perubahan_status' => 'Perubahan Status Pegawai', 'lainnya' => 'Lainnya',
    ];

    protected $table = 'penempatan_pegawai';

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class);
    }

    public function pencatat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pencatat_id');
    }

    public static function buatAwal(Pegawai $pegawai, ?int $pencatatId = null): self
    {
        $riwayat = $pegawai->riwayatPenempatan()->orderBy('tanggal_mulai')->first();
        if ($riwayat) {
            return $riwayat;
        }

        return self::query()->create([
            ...self::salinPenempatan($pegawai), 'jenis' => 'awal',
            'tanggal_mulai' => $pegawai->tanggal_masuk->toDateString(),
            'keterangan' => 'Penempatan awal pegawai.', 'pencatat_id' => $pencatatId,
            'nama_pencatat' => $pencatatId ? User::query()->find($pencatatId)?->name : null,
        ]);
    }

    /** @param array<string, mixed> $data */
    public static function catat(Pegawai $pegawai, array $data, int $pencatatId): self
    {
        return DB::transaction(function () use ($pegawai, $data, $pencatatId): self {
            $pegawai = Pegawai::query()->lockForUpdate()->findOrFail($pegawai->id);
            $terakhir = $pegawai->riwayatPenempatan()->orderByDesc('tanggal_mulai')->first();
            if ((string) $terakhir?->id !== (string) ($data['versi_penempatan'] ?? '')) {
                throw ValidationException::withMessages(['pegawai_id' => 'Penempatan pegawai sudah berubah. Muat ulang halaman sebelum menyimpan kembali.']);
            }
            $terakhir ??= self::buatAwal($pegawai, $pencatatId);
            $tanggal = Carbon::parse($data['tanggal_mulai'])->startOfDay();
            if ($tanggal->toDateString() > now('Asia/Jakarta')->toDateString() || $tanggal->lte($terakhir->tanggal_mulai) || $tanggal->lt($pegawai->tanggal_masuk)) {
                throw ValidationException::withMessages(['tanggal_mulai' => 'Tanggal berlaku harus setelah penempatan terakhir ('.$terakhir->tanggal_mulai->format('d/m/Y').'), tidak sebelum tanggal masuk, dan maksimal hari ini.']);
            }
            $adaPerubahan = false;
            foreach (self::KOLOM_PENEMPATAN as $kolom) {
                $adaPerubahan = $adaPerubahan || (string) $pegawai->{$kolom} !== (string) ($data[$kolom] ?? '');
            }
            if (! $adaPerubahan) {
                throw ValidationException::withMessages(['jabatan_id' => 'Ubah minimal salah satu: jabatan, kantor, status pegawai, atau shift.']);
            }
            $terakhir->update(['tanggal_selesai' => $tanggal->copy()->subDay()->toDateString()]);
            $pegawai->fill(array_intersect_key($data, array_flip(self::KOLOM_PENEMPATAN)))->save();

            return self::query()->create([
                ...self::salinPenempatan($pegawai),
                'jenis' => $data['jenis'], 'tanggal_mulai' => $tanggal->toDateString(),
                'nomor_sk' => $data['nomor_sk'] ?? null, 'keterangan' => $data['keterangan'],
                'pencatat_id' => $pencatatId, 'nama_pencatat' => User::query()->findOrFail($pencatatId)->name,
            ]);
        }, 3);
    }

    /** @return array<string, mixed> */
    private static function salinPenempatan(Pegawai $pegawai): array
    {
        $pegawai->load(['jabatan.unitOrganisasi', 'divisi', 'kantor', 'statusPegawai', 'shiftPegawai']);

        return [
            'pegawai_id' => $pegawai->id,
            ...$pegawai->only(self::KOLOM_PENEMPATAN),
            'nama_jabatan' => $pegawai->jabatan->nama, 'nama_unit' => $pegawai->jabatan->unitOrganisasi->nama,
            'nama_divisi' => $pegawai->divisi?->nama, 'nama_kantor' => $pegawai->kantor->nama_kantor,
            'nama_status_pegawai' => $pegawai->statusPegawai->nama, 'nama_shift' => $pegawai->shiftPegawai->nama_shift,
            'jam_masuk' => $pegawai->shiftPegawai->jam_masuk, 'jam_pulang' => $pegawai->shiftPegawai->jam_pulang,
            'durasi_shift' => $pegawai->shiftPegawai->durasi, 'wajib_qr' => $pegawai->shiftPegawai->wajib_qr,
        ];
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['tanggal_mulai' => 'date', 'tanggal_selesai' => 'date', 'wajib_qr' => 'boolean', 'durasi_shift' => 'integer'];
    }
}

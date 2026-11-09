<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['absensi_id', 'masuk_lama', 'pulang_lama', 'masuk_baru', 'pulang_baru', 'alasan', 'nama_pencatat'])]
class RiwayatAbsensi extends Model
{
    use HasUuids;

    protected $table = 'riwayat_absensi';

    protected function casts(): array
    {
        return ['masuk_lama' => 'datetime', 'pulang_lama' => 'datetime', 'masuk_baru' => 'datetime', 'pulang_baru' => 'datetime'];
    }
}

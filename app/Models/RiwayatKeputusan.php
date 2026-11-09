<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['ketidakhadiran_id', 'lembur_id', 'status_lama', 'status_baru', 'nama_pemutus', 'pemutus_id', 'catatan'])]
class RiwayatKeputusan extends Model
{
    use HasUuids;

    protected $table = 'riwayat_keputusan';
}

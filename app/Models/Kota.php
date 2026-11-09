<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['kode', 'nama', 'kode_provinsi', 'nama_provinsi', 'sinkron_pada'])]
class Kota extends Model
{
    protected $table = 'kota';

    public function kantor(): HasMany
    {
        return $this->hasMany(Kantor::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['sinkron_pada' => 'datetime'];
    }
}

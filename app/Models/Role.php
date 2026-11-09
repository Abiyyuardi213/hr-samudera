<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nama', 'deskripsi', 'status'])]
class Role extends Model
{
    use HasUuids;

    protected $table = 'role';

    public function pengguna(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['status' => 'boolean'];
    }
}

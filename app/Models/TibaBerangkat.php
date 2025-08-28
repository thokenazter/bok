<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TibaBerangkat extends Model
{
    protected $table = 'tiba_berangkat';

    protected $fillable = [
        'no_surat',
    ];

    public function details(): HasMany
    {
        return $this->hasMany(TibaBerangkatDetail::class);
    }
}

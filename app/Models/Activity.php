<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Activity extends Model
{
    use LogsActivity;

    protected $fillable = [
        'nama',
    ];

    public function lpjs(): HasMany
    {
        return $this->hasMany(Lpj::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nama'])
            ->logOnlyDirty();
    }
}

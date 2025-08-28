<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Employee extends Model
{
    use LogsActivity;

    protected $fillable = [
        'nama',
        'nip',
        'tanggal_lahir',
        'pangkat_golongan',
        'jabatan',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
    ];

    public function lpjParticipants(): HasMany
    {
        return $this->hasMany(LpjParticipant::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nama', 'nip', 'pangkat_golongan', 'jabatan'])
            ->logOnlyDirty();
    }

    /**
     * Get total saldo (transport + per diem) for this employee
     */
    public function getTotalSaldoAttribute()
    {
        return $this->lpjParticipants()->sum('total_amount');
    }

    /**
     * Get total transport amount for this employee
     */
    public function getTotalTransportAttribute()
    {
        return $this->lpjParticipants()->sum('transport_amount');
    }

    /**
     * Get total per diem amount for this employee
     */
    public function getTotalPerDiemAttribute()
    {
        return $this->lpjParticipants()->sum('per_diem_amount');
    }

    /**
     * Get count of LPJ participations
     */
    public function getTotalLpjCountAttribute()
    {
        return $this->lpjParticipants()->count();
    }
}

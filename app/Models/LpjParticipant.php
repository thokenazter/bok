<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class LpjParticipant extends Model
{
    use LogsActivity;

    protected $fillable = [
        'lpj_id',
        'employee_id',
        'role',
        'lama_tugas_hari',
        'transport_amount',
        'per_diem_rate',
        'per_diem_days',
        'per_diem_amount',
        'total_amount',
    ];

    protected $casts = [
        'transport_amount' => 'decimal:2',
        'per_diem_rate' => 'decimal:2',
        'per_diem_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function lpj(): BelongsTo
    {
        return $this->belongsTo(Lpj::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['role', 'lama_tugas_hari', 'transport_amount', 'per_diem_amount', 'total_amount'])
            ->logOnlyDirty();
    }
}

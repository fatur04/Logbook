<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OvertimeSummary extends Model
{
    protected $fillable = [
        'activity_id',
        'initial',
        'nama',
        'bulan',
        'total_jam',
        'total_lembur',
    ];

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function overtime()
    {
        return $this->belongsTo(Overtime::class, 'activity_id', 'activity_id');
    }

}


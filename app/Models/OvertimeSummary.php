<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

}


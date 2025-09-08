<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Overtime;

class Overtime extends Model
{
    protected $fillable = [
        'activity_id',
        'user_id',
        'initial',
        'nama',
        'cluster',
        'role',
        'start_date',
        'end_date',
        'total_jam',
        'total_lembur',
        'status',
    ];
    
    // 🔹 Rekap lembur per bulan per karyawan
    public static function rekapBulanan($bulan, $tahun)
    {
        return self::select(
                'initial',
                'nama',
                DB::raw('SUM(total_jam) as total_jam'),
                DB::raw('SUM(total_lembur) as total_lembur')
            )
            ->whereMonth('start_date', $bulan)
            ->whereYear('start_date', $tahun)
            ->groupBy('initial', 'nama')
            ->get();
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activity_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

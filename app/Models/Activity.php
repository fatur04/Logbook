<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Overtime;
use Carbon\Carbon;
use App\Models\OvertimeSummary;
use Illuminate\Support\Facades\Mail;
use App\Mail\OvertimeRequestMail;

class Activity extends Model
{
    protected $fillable = [
        'id',
        'initial',
        'nama',
        'cluster',
        'role',
        'start_date',
        'end_date',
        'aplikasi',
        'activity',
    ];

    protected static function boot()
    {
        parent::boot();

        // Saat Activity dibuat
        static::created(function ($activity) {
            if (session('hitung_lembur')) {
                $activity->generateOvertime();
            }
        });

        static::updated(function ($activity) {
            if (session('hitung_lembur')) {
                $activity->generateOvertime();
            }
        });

        // Saat Activity dihapus
        static::deleting(function ($activity) {
            Overtime::where('activity_id', $activity->id)->delete();

            // Update summary per initial
            $bulan = Carbon::parse($activity->start_date)->format('Y-m');
            $summary = OvertimeSummary::where('initial', $activity->initial)
                ->where('bulan', $bulan)
                ->first();

            if ($summary) {
                $summary->total_jam = Overtime::where('initial', $activity->initial)
                    ->whereMonth('start_date', Carbon::parse($activity->start_date)->month)
                    ->whereYear('start_date', Carbon::parse($activity->start_date)->year)
                    ->sum('total_jam');

                $summary->total_lembur = Overtime::where('initial', $activity->initial)
                    ->whereMonth('start_date', Carbon::parse($activity->start_date)->month)
                    ->whereYear('start_date', Carbon::parse($activity->start_date)->year)
                    ->sum('total_lembur');

                if ($summary->total_jam == 0 && $summary->total_lembur == 0) {
                    $summary->delete(); // hapus jika sudah kosong
                } else {
                    $summary->save();
                }
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id');
    }

    // Relasi ke Overtime
    public function overtime()
    {
        return $this->hasOne(Overtime::class);
    }

    // Relasi ke OvertimeSummary
    public function overtimeSummary()
    {
        return $this->hasOne(OvertimeSummary::class);
    }

    // Hitung total jam & lembur, simpan ke Overtime & OvertimeSummary
    public function generateOvertime()
    {
        
        if ($this->start_date && $this->end_date) {
            $start = Carbon::parse($this->start_date);
            $end   = Carbon::parse($this->end_date);

            if ($end->lessThan($start)) {
                $end->addDay();
            }

            $totalJam = $start->diffInMinutes($end) / 60;
            // 🚫 Jika ≤ 8 jam, tidak dianggap lembur
            if ($totalJam <= 9) {
                // Pastikan juga kalau ada lembur sebelumnya dihapus
                Overtime::where('activity_id', $this->id)->delete();
                return;
            }
            $lembur = max(0, $totalJam - 9);

            // ------------------------------
            // Update atau buat Overtime
            // ------------------------------
            $overtime = Overtime::updateOrCreate(
                ['activity_id' => $this->id],
                [
                    'activity_id' => $this->id,
                    'user_id' => $this->user_id ?? auth()->id(), // isi otomatis
                    'initial' => $this->initial,
                    'nama' => $this->nama,
                    'cluster' => $this->cluster,
                    'role' => $this->role,
                    'start_date' => $this->start_date,
                    'end_date' => $this->end_date,
                    'total_jam' => round($totalJam, 2),
                    'total_lembur' => round($lembur, 2),
                    'status' => 'pending',
                ]
            );

            // Kirim email ke Outlook
            $supervisor = \App\Models\User::role('supervisor')->first();
            if ($supervisor) {
                Mail::to($supervisor->email)->queue(
                    new OvertimeRequestMail($overtime, 'Engineer')
                );
            }

            // ==========================
            // REKAP BULANAN PER REKAN (initial)
            // ==========================
            // $bulan = $start->format('Y-m');

            // $summary = OvertimeSummary::firstOrNew([
            //     'initial' => $this->initial,
            //     'bulan'   => $bulan,
            // ]);

            // $summary->nama = $this->nama;
            // $summary->total_jam = Overtime::where('initial', $this->initial)
            //     ->whereMonth('start_date', $start->month)
            //     ->whereYear('start_date', $start->year)
            //     ->sum('total_jam');

            // $summary->total_lembur = Overtime::where('initial', $this->initial)
            //     ->whereMonth('start_date', $start->month)
            //     ->whereYear('start_date', $start->year)
            //     ->sum('total_lembur');

            // // Jika mau simpan activity_id terakhir
            // $summary->activity_id = $this->id;

            // $summary->save();
        }
    }

    protected static function booted()
{
    static::creating(function ($activity) {
        $activity->initial = $activity->initial ?? auth()->user()->initial;
        $activity->nama = $activity->nama ?? auth()->user()->name;
    });
}
}

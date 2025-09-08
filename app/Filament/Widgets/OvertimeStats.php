<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use App\Models\OvertimeSummary;
use Carbon\Carbon;

class OvertimeStats extends BaseWidget
{
    protected static bool $polling = true;
    protected static ?string $pollingInterval = '5000'; // 5 detik

    protected function getCards(): array
    {
        $bulan = Carbon::now()->format('Y-m');

        $totalLembur = OvertimeSummary::where('bulan', $bulan)->sum('total_lembur');
        $avgLembur = OvertimeSummary::where('bulan', $bulan)->avg('total_lembur');
        $topKaryawan = OvertimeSummary::where('bulan', $bulan)->orderBy('total_lembur','desc')->first();
        $avgJam = OvertimeSummary::where('bulan', $bulan)->avg('total_jam');

        return [
            Card::make('Total Lembur Bulan Ini', round($totalLembur,2).' Jam')->color('primary')->icon('heroicon-o-clock'),
            Card::make('Rata-rata Lembur', round($avgLembur,2).' Jam')->color('success')->icon('heroicon-o-chart-bar'),
            //Card::make('Karyawan Top', $topKaryawan ? $topKaryawan->nama.' ('.round($topKaryawan->total_lembur,2).' Jam)' : '-')->color('warning')->icon('heroicon-o-star'),
            Card::make(
                'Karyawan Top',
                $topKaryawan
                    ? $topKaryawan->nama . ' (' . round($topKaryawan->total_lembur, 2) . ' Jam)'
                    : '-'
            )
                ->color('warning')
                ->icon('heroicon-o-star'),
            Card::make('Rata-rata Jam Kerja', round($avgJam,2).' Jam')->color('secondary')->icon('heroicon-o-clock'),
        ];
    }
}

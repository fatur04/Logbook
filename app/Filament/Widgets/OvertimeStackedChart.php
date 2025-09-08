<?php

namespace App\Filament\Widgets;

use Filament\Widgets\BarChartWidget;
use App\Models\OvertimeSummary;

class OvertimeStackedChart extends BarChartWidget
{
    protected static ?string $heading = 'Jam Kerja & Lembur Stacked';
    protected static bool $polling = true;
    protected static ?string $pollingInterval = '5000';

    protected function getType(): string { return 'bar'; }

    protected function getData(): array
    {
        $data = OvertimeSummary::selectRaw('nama, SUM(total_jam) as total_jam, SUM(total_lembur) as total_lembur')
            ->groupBy('nama')
            ->orderBy('total_lembur','desc')
            ->get();

        $labels = $data->pluck('nama');
        $jamValues = $data->pluck('total_jam');
        $lemburValues = $data->pluck('total_lembur');

        return [
            'labels' => $labels,
            'datasets' => [
                ['label'=>'Jam Kerja','data'=>$jamValues,'backgroundColor'=>'#22c55e','borderRadius'=>10],
                ['label'=>'Lembur','data'=>$lemburValues,'backgroundColor'=>'#6366f1','borderRadius'=>10],
            ]
        ];
    }

    protected function getOptions(): array
    {
        return [
            'responsive'=>true,
            'maintainAspectRatio'=>false,
            'plugins'=>[
                'tooltip'=>[
                    'enabled'=>true,
                ],
                'legend'=>['position'=>'top','labels'=>['color'=>'#6B7280']]
            ],
            'scales'=>[
                'x'=>['stacked'=>true],
                'y'=>['stacked'=>true,'beginAtZero'=>true],
            ]
        ];
    }
}

<?php

namespace App\Filament\Widgets;

use App\Models\Activity;
use Filament\Widgets\ChartWidget;
use Filament\Forms;

class ActivityUsageChart extends ChartWidget
{
    protected static ?string $heading = '📊 Penggunaan Aplikasi per User';

    public ?string $filterMonth = null;
    public ?array $filterClusters = [];

    protected static ?array $filters = null; // wajib ada biar form schema kebaca

    protected function getData(): array
    {
        $query = Activity::query();

        // 🔎 Filter bulan
        if ($this->filterMonth) {
            $query->whereMonth('start_date', date('m', strtotime($this->filterMonth)))
                  ->whereYear('start_date', date('Y', strtotime($this->filterMonth)));
        }

        // 🔎 Filter multiple cluster
        if (!empty($this->filterClusters)) {
            $query->whereIn('cluster', $this->filterClusters);
        }

        $activities = $query->select('initial', 'aplikasi') // ✅ diganti aplikasi
            ->selectRaw('COUNT(*) as total')
            ->groupBy('initial', 'aplikasi')
            ->get();

        $initials = $activities->pluck('initial')->unique()->values();
        $applications = $activities->pluck('aplikasi')->unique()->values();

        // 🎨 Warna unik untuk setiap aplikasi (HSL stabil)
        $appColorMap = [];
        $totalApps = count($applications);

        foreach ($applications as $index => $app) {
            $hue = ($index * (360 / max(1, $totalApps)));
            $appColorMap[$app] = "hsl($hue, 70%, 60%)";
        }

        $datasets = [];
        foreach ($applications as $app) {
            $datasets[] = [
                'label' => $app,
                'data' => $initials->map(function ($initial) use ($activities, $app) {
                    return $activities
                        ->where('initial', $initial)
                        ->where('aplikasi', $app)
                        ->first()
                        ->total ?? 0;
                })->toArray(),
                'backgroundColor' => $appColorMap[$app],
                'stack' => 'Stack 0',
            ];
        }

        return [
            'labels' => $initials,
            'datasets' => $datasets,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                    'labels' => [
                        'boxWidth' => 20,
                        'padding' => 15,
                    ],
                ],
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
        ];
    }

    // =====================
    // 🎛️ Filter Bulan + Cluster
    // =====================
    protected function getFormSchema(): array
    {
        return [
            Forms\Components\DatePicker::make('filterMonth')
                ->label('Filter Bulan')
                ->displayFormat('F Y')
                ->native(false)
                ->closeOnDateSelection()
                ->format('Y-m')
                ->withoutTime(),

            Forms\Components\MultiSelect::make('filterClusters')
                ->label('Filter Cluster')
                ->options(
                    Activity::query()
                        ->select('cluster')
                        ->distinct()
                        ->pluck('cluster', 'cluster')
                )
                ->searchable()
                ->placeholder('Pilih Cluster'),
        ];
    }
}

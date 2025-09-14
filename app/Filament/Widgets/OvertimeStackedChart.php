<?php

namespace App\Filament\Widgets;

use Filament\Widgets\BarChartWidget;
use App\Models\OvertimeSummary;
use App\Models\Overtime;
use Illuminate\Support\Facades\Auth;

class OvertimeStackedChart extends BarChartWidget
{
    protected static ?string $heading = 'Jam Kerja & Lembur Stacked';
    protected static bool $polling = true;
    protected static ?string $pollingInterval = '5000';

    public array $filters = [
        'bulan' => null,
        'cluster' => null,
    ];

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $user = Auth::user();
        $query = OvertimeSummary::with('overtime');

        $rolesAllAccess = ['super_admin', 'admin', 'manager', 'supervisor'];
        if (!in_array($user->getRoleNames()->first(), $rolesAllAccess)) {
            $query->whereHas('overtime', fn($q) => $q->where('initial', $user->initial));
        }

        // Filter bulan
        if (!empty($this->filters['bulan'])) {
            $query->whereMonth('created_at', $this->filters['bulan']);
        }

        // Filter cluster
        if (!empty($this->filters['cluster'])) {
            $query->whereHas('overtime', fn($q) => $q->where('cluster', $this->filters['cluster']));
        }

        $data = $query->selectRaw('nama, SUM(total_jam) as total_jam, SUM(total_lembur) as total_lembur')
            ->groupBy('nama')
            ->orderByDesc('total_lembur')
            ->get();

        $labels = $data->map(fn($item) => (string)$item->nama);
        $jamValues = $data->map(fn($item) => (float)$item->total_jam);
        $lemburValues = $data->map(fn($item) => (float)$item->total_lembur);

        return [
            'labels' => $labels,
            'datasets' => [
                ['label' => 'Jam Kerja', 'data' => $jamValues, 'backgroundColor' => '#22c55e', 'borderRadius' => 10],
                ['label' => 'Lembur', 'data' => $lemburValues, 'backgroundColor' => '#6366f1', 'borderRadius' => 10],
            ],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'tooltip' => ['enabled' => true],
                'legend' => ['position' => 'top', 'labels' => ['color' => '#6B7280']],
            ],
            'scales' => [
                'x' => ['stacked' => true],
                'y' => ['stacked' => true, 'beginAtZero' => true],
            ],
        ];
    }

    // Tambahkan mount untuk default filter
    public function mount(): void
    {
        $this->filters['bulan'] = now()->month;
        $this->filters['cluster'] = null;
    }

    // Tambahkan opsi filter dropdown
    protected function getFilterFormSchema(): array
    {
        // Ambil semua cluster dari table overtime
        $clusters = Overtime::distinct()->pluck('cluster')->toArray();

        return [
            \Filament\Forms\Components\Select::make('bulan')
                ->label('Bulan')
                ->options([
                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
                ])
                ->reactive()
                ->afterStateUpdated(fn() => $this->dispatchBrowserEvent('refreshChart')),

            \Filament\Forms\Components\Select::make('cluster')
                ->label('Cluster')
                ->options($clusters)
                ->placeholder('Semua Cluster')
                ->reactive()
                ->afterStateUpdated(fn() => $this->dispatchBrowserEvent('refreshChart')),
        ];
    }
}

<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use App\Models\OvertimeSummary;
use Carbon\Carbon;

class TopEmployeeProgress extends Widget
{
    protected static string $view = 'filament.widgets.top-employee-progress';
    protected static bool $polling = true;
    protected static ?int $pollingInterval = 5000;

    public $topEmployees;

    public function mount(): void
    {
        $this->loadTopEmployees();
    }

    public function loadTopEmployees()
    {
        $bulan = Carbon::now()->format('Y-m');
        $this->topEmployees = OvertimeSummary::where('bulan', $bulan)
            ->orderBy('total_lembur', 'desc')
            ->take(5)
            ->get();
    }
}

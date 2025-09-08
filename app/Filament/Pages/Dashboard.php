<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Filament\Widgets\OvertimeStats;
use App\Filament\Widgets\TopEmployeeProgress;
use App\Filament\Widgets\OvertimeStackedChart;
use App\Filament\Widgets\ActivityUsageChart;

class Dashboard extends Page
{
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $slug = 'dashboard';
    protected static string $view = 'filament.pages.dashboard';

    // Tambahkan header & footer widget melalui method
    public function getHeaderWidgets(): array
    {
        return [
            OvertimeStats::class,
            TopEmployeeProgress::class,
        ];
    }

    public function getFooterWidgets(): array
    {
        return [
            OvertimeStackedChart::class,
            ActivityUsageChart::class,
        ];
    }
}

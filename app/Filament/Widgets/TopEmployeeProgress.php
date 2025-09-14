<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use App\Models\OvertimeSummary;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

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
        $user = Auth::user();
        $bulan = Carbon::now()->format('Y-m');

        $query = OvertimeSummary::where('bulan', $bulan);

        $role = $user->getRoleNames()->first();
        if (!in_array($role, ['super_admin', 'admin'])) {
            $query->where('initial', $user->initial);
        }
        
        $this->topEmployees = OvertimeSummary::where('bulan', $bulan)
            ->orderBy('total_lembur', 'desc')
            ->take(5)
            ->get();
    }
}

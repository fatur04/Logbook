<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OvertimeController;
use App\Models\Overtime;
use App\Models\OvertimeSummary;
use Carbon\Carbon;


Route::get('/', function () {
    return redirect()->route('filament.admin.auth.login');
});

Route::get('/overtimes/approve/{id}', function ($id) {
    $overtime = Overtime::findOrFail($id);
    $overtime->update(['status' => 'approved']);

    $start = Carbon::parse($overtime->start_date);

    // Hitung summary setelah disetujui
    $summary = OvertimeSummary::firstOrNew([
        'initial' => $overtime->initial,
        'bulan'   => $start->format('Y-m'),
    ]);

    $summary->nama = $overtime->nama;
    $summary->total_jam = Overtime::where('initial', $overtime->initial)
        ->where('status', 'approved') // hanya yang sudah disetujui
        ->whereMonth('start_date', $start->month)
        ->whereYear('start_date', $start->year)
        ->sum('total_jam');

    $summary->total_lembur = Overtime::where('initial', $overtime->initial)
        ->where('status', 'approved')
        ->whereMonth('start_date', $start->month)
        ->whereYear('start_date', $start->year)
        ->sum('total_lembur');

    $summary->activity_id = $overtime->activity_id;
    $summary->save();

    return "Lembur sudah disetujui.";
});

Route::get('/overtimes/reject/{id}', function ($id) {
    $overtime = Overtime::findOrFail($id);
    $overtime->update(['status' => 'rejected']);

    return "Lembur ditolak.";
});

Route::get('/test-email', function () {
    Mail::raw('Test Email dari Laravel', function ($msg) {
        $msg->to('achmadfatur11@outlook.com')->subject('Test SMTP');
    });

    return 'Email terkirim (cek inbox HR)';
});
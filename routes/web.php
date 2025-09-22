<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OvertimeApprovalController;
use App\Models\Overtime;
use App\Models\OvertimeSummary;
use Carbon\Carbon;
use App\Filament\Pages\OvertimeApprovalPage;

Route::get('/', function () {
    return redirect()->route('filament.admin.auth.login');
});

Route::get('/overtime-approval/{token}', function ($token) {
    // Jika belum login, simpan URL tujuan & redirect ke login
    if (! auth()->check()) {
        session(['url.intended' => route('filament.pages.overtime-approval', ['token' => $token])]);
        return redirect()->route('filament.auth.login');
    }

    // Jika sudah login → langsung ke Filament Page
    return redirect()->route('filament.pages.overtime-approval', ['token' => $token]);
})->name('overtime-approval-link');

Route::get('/test-email', function () {
    Mail::raw('Test Email dari Laravel', function ($msg) {
        $msg->to('achmadfatur11@outlook.com')->subject('Test SMTP');
    });

    return 'Email terkirim (cek inbox HR)';
});
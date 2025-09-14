<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OvertimeApprovalController;
use App\Models\Overtime;
use App\Models\OvertimeSummary;
use Carbon\Carbon;


Route::get('/', function () {
    return redirect()->route('filament.admin.auth.login');
});

Route::get('/overtime/approve/{token}', [OvertimeApprovalController::class, 'approve'])
    ->name('overtimes.approve');

Route::get('/overtime/reject/{token}', [OvertimeApprovalController::class, 'reject'])
    ->name('overtimes.reject');

Route::get('/test-email', function () {
    Mail::raw('Test Email dari Laravel', function ($msg) {
        $msg->to('achmadfatur11@outlook.com')->subject('Test SMTP');
    });

    return 'Email terkirim (cek inbox HR)';
});
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Overtime;
use App\Models\OvertimeSummary;
use Illuminate\Support\Facades\Mail;
use App\Mail\OvertimeRequestMail;
use App\Models\User;
use Carbon\Carbon;

class OvertimeApprovalController extends Controller
{
    public function approve($token)
    {
        $overtime = Overtime::where('approval_token', $token)->firstOrFail();

        // Lakukan aksi approve atau tampilkan halaman approval Filament
        return redirect()->route('filament.admin.pages.overtime-approval', [
            'token' => $overtime->approval_token,
        ]);  
    }
    // public function approve($token)
    // {
    //     $overtime = Overtime::where('approval_token', $token)->firstOrFail();

    //     $role = $overtime->user?->roles()->first()?->name; // jika ingin cek role pengaju
    //     $approver = auth()->user(); // opsional, kalau pakai login
    //     $approverRole = $approver?->roles()->first()?->name ?? null;

    //     // ✅ Jika admin/super_admin bisa approve langsung
    //     if (in_array($approverRole, ['super_admin', 'admin'])) {
    //         $overtime->update(['status' => 'approved']);

    //         $start = Carbon::parse($overtime->start_date);
    //         $summary = OvertimeSummary::firstOrNew([
    //             'initial' => $overtime->initial,
    //             'bulan' => $start->format('Y-m'),
    //         ]);
    //         $summary->nama = $overtime->nama;
    //         $summary->total_jam = Overtime::where('initial', $overtime->initial)
    //             ->where('status', 'approved')
    //             ->whereMonth('start_date', $start->month)
    //             ->whereYear('start_date', $start->year)
    //             ->sum('total_jam');
    //         $summary->total_lembur = Overtime::where('initial', $overtime->initial)
    //             ->where('status', 'approved')
    //             ->whereMonth('start_date', $start->month)
    //             ->whereYear('start_date', $start->year)
    //             ->sum('total_lembur');
    //         $summary->activity_id = $overtime->activity_id;
    //         $summary->save();

    //         // Email ke User pengaju
    //         Mail::to($overtime->user->email)->queue(new OvertimeRequestMail($overtime, $overtime->nama, 'approve'));

    //         return "Lembur disetujui Admin/Super Admin, email dikirim ke User.";
    //     }

    //     // ==========================
    //     // Supervisor / Manager flow
    //     // ==========================
    //     if ($overtime->status === 'pending') {
    //         // Supervisor approve
    //         $overtime->update(['status' => 'Engineer_approved']);
            
    //         $manager = User::whereHas('roles', fn($q) => $q->where('name', 'manager'))->first();
    //         if ($manager) {
    //             Mail::to($manager->email)->queue(new OvertimeRequestMail($overtime, 'Manager', 'pending'));
    //         }

    //         return "✅ Lembur disetujui Engineer, email dikirim ke Manager.";
    //     }

    //     if ($overtime->status === 'Engineer_approved') {
    //         // Manager approve
    //         $overtime->update(['status' => 'approved']);

    //         $start = Carbon::parse($overtime->start_date);
    //         $summary = OvertimeSummary::firstOrNew([
    //             'initial' => $overtime->initial,
    //             'bulan' => $start->format('Y-m'),
    //         ]);
    //         $summary->nama = $overtime->nama;
    //         $summary->total_jam = Overtime::where('initial', $overtime->initial)
    //             ->where('status', 'approved')
    //             ->whereMonth('start_date', $start->month)
    //             ->whereYear('start_date', $start->year)
    //             ->sum('total_jam');
    //         $summary->total_lembur = Overtime::where('initial', $overtime->initial)
    //             ->where('status', 'approved')
    //             ->whereMonth('start_date', $start->month)
    //             ->whereYear('start_date', $start->year)
    //             ->sum('total_lembur');
    //         $summary->activity_id = $overtime->activity_id;
    //         $summary->save();

    //         Mail::to($overtime->user->email)->queue(new OvertimeRequestMail($overtime, $overtime->nama, 'approve'));

    //         return "✅ Overtime {$overtime->nama} berhasil di-approve!";
    //     }

    //     return "Lembur sudah diproses sebelumnya atau tidak dapat di-approve.";
    // }

    // public function reject($token)
    // {
    //     $overtime = Overtime::where('approval_token', $token)->firstOrFail();
    //     $overtime->update(['status' => 'rejected']);

    //     $start = Carbon::parse($overtime->start_date);

    //     OvertimeSummary::where('initial', $overtime->initial)
    //         ->where('bulan', $start->format('Y-m'))
    //         ->delete();

    //     Mail::to($overtime->user->email)->queue(new OvertimeRequestMail($overtime, $overtime->nama, 'reject'));

    //     return "❌ Overtime {$overtime->nama} ditolak.";
    // }
}

<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Overtime;
use App\Models\OvertimeSummary;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Mail;
use App\Mail\OvertimeRequestMail;

class OvertimeApprovalPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-check-circle';
    protected static string $view = 'filament.pages.overtime-approval-page';
    protected static ?string $slug = 'overtime-approval/{token}';
    protected static bool $shouldRegisterNavigation = false;

    public static function getRoutes(): \Closure
    {
        return function ($router) {
            $router->get('/overtime-approval/{token}', static::class)
                ->name('filament.pages.overtime-approval');
        };
    }

    public ?Overtime $overtime = null;

    public function mount(string $token): void
    {
        $this->overtime = Overtime::where('approval_token', $token)->firstOrFail();
    }

    public function approve(): void
    {
        $user = auth()->user();
        $role = $user->roles()->first()?->name;

        if (! $this->overtime) {
            Notification::make()->title('Data lembur tidak ditemukan')->danger()->send();
            return;
        }

        $record = $this->overtime;

        // ✅ Super Admin & Admin bisa approve semua
        if (in_array($role, ['super_admin', 'admin'])) {
            $record->update(['status' => 'approved', 'approved_by' => $user->id]);
            Mail::to($record->user->email)->queue(new OvertimeRequestMail($record, $record->nama));
        }
        // ✅ Manager hanya approve dari status Engineer_approved
        elseif ($role === 'manager' && $record->status === 'Engineer_approved') {
            $record->update(['status' => 'approved', 'approved_by' => $user->id]);
            Mail::to($record->user->email)->queue(new OvertimeRequestMail($record, $record->nama));
        }
        // ✅ Supervisor hanya approve dari status pending
        elseif ($role === 'supervisor' && $record->status === 'pending') {
            $record->update(['status' => 'Engineer_approved', 'approved_by' => $user->id]);

            // Ambil semua email manager
            $managers = \App\Models\User::role('manager')->pluck('email')->toArray();
            $creator = $record->user?->email;
            $supervisor = $user->email;
            $ccRecipients = array_filter([$creator, $supervisor]);

            if (!empty($managers)) {
                Mail::to($managers)
                    ->cc($ccRecipients)
                    ->queue(new OvertimeRequestMail($record, 'Manager'));
            }
        } else {
            Notification::make()
                ->title('Anda tidak punya hak approve atau status belum sesuai')
                ->warning()
                ->send();
            return;
        }

        // ✅ Update summary hanya kalau status final approved
        if ($record->status === 'approved') {
            $start = Carbon::parse($record->start_date);
            $summary = OvertimeSummary::firstOrNew([
                'initial' => $record->initial,
                'bulan'   => $start->format('Y-m'),
            ]);
            $summary->nama = $record->nama;
            $summary->total_jam = Overtime::where('initial', $record->initial)
                ->where('status', 'approved')
                ->whereMonth('start_date', $start->month)
                ->whereYear('start_date', $start->year)
                ->sum('total_jam');
            $summary->total_lembur = Overtime::where('initial', $record->initial)
                ->where('status', 'approved')
                ->whereMonth('start_date', $start->month)
                ->whereYear('start_date', $start->year)
                ->sum('total_lembur');
            $summary->activity_id = $record->activity_id;
            $summary->save();
        }

        Notification::make()
            ->title('✔ Lembur berhasil diapprove')
            ->success()
            ->send();

        $this->redirect(OvertimeApprovalPage::getUrl(['token' => $record->approval_token]));
    }

    public function reject(): void
    {
        if (! $this->overtime) return;

        $this->overtime->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
        ]);

        Notification::make()
            ->title('Lembur ditolak')
            ->danger()
            ->send();

        $this->redirect(OvertimeApprovalPage::getUrl(['token' => $this->overtime->approval_token]));
    }
}

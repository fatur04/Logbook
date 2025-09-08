<?php

namespace App\Filament\Resources\ActivityResource\Pages;

use App\Filament\Resources\ActivityResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use App\Models\Overtime;
use App\Models\OvertimeSummary;
use Carbon\Carbon;

class EditActivity extends EditRecord
{
    protected static string $resource = ActivityResource::class;

    protected function afterSave(): void
    {
        $data = $this->form->getState();

        if (($data['hitung_lembur'] ?? 0) == 1) {
            // ✅ Kalau toggle hijau → hitung / update lembur
            $this->record->generateOvertime();

            Notification::make()
                ->title('Lembur dihitung setelah update')
                ->success()
                ->send();
        } else {
            // ❌ Kalau toggle merah → hapus lembur & summary
            Overtime::where('activity_id', $this->record->id)->delete();

            // Update ulang summary berdasarkan sisa lembur user
            $bulan = Carbon::parse($this->record->start_date)->format('Y-m');
            $summary = OvertimeSummary::where('initial', $this->record->initial)
                ->where('bulan', $bulan)
                ->first();

            if ($summary) {
                $summary->total_jam = Overtime::where('initial', $this->record->initial)
                    ->whereMonth('start_date', Carbon::parse($this->record->start_date)->month)
                    ->whereYear('start_date', Carbon::parse($this->record->start_date)->year)
                    ->sum('total_jam');

                $summary->total_lembur = Overtime::where('initial', $this->record->initial)
                    ->whereMonth('start_date', Carbon::parse($this->record->start_date)->month)
                    ->whereYear('start_date', Carbon::parse($this->record->start_date)->year)
                    ->sum('total_lembur');

                $summary->save();
            }

            Notification::make()
                ->title('Lembur dihapus')
                ->danger()
                ->send();
        }
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
    protected function mutateFormDataBeforeSave(array $data): array
{
    if (($data['aplikasi'] ?? null) === 'other' && !empty($data['aplikasi_manual'])) {
        $data['aplikasi'] = $data['aplikasi_manual']; // ganti dengan teks manual
    }

    unset($data['aplikasi_manual']);
    return $data;
}
}

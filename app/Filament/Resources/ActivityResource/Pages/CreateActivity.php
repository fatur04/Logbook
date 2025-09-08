<?php

namespace App\Filament\Resources\ActivityResource\Pages;

use App\Filament\Resources\ActivityResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateActivity extends CreateRecord
{
    protected static string $resource = ActivityResource::class;

    protected function afterCreate(): void
    {
        $data = $this->form->getState();

        if (($data['hitung_lembur'] ?? 0) == 1) {
            $this->record->generateOvertime();

            Notification::make()
                ->title('Lembur dihitung saat create')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Lembur tidak dihitung')
                ->danger()
                ->send();
        }
    }
    protected function mutateFormDataBeforeCreate(array $data): array
{
    if (($data['aplikasi'] ?? null) === 'other' && !empty($data['aplikasi_manual'])) {
        $data['aplikasi'] = $data['aplikasi_manual']; // ganti dengan teks manual
    }

    unset($data['aplikasi_manual']); // hapus biar gak nyasar
    return $data;
}
}

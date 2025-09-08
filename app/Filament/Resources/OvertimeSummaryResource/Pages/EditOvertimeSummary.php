<?php

namespace App\Filament\Resources\OvertimeSummaryResource\Pages;

use App\Filament\Resources\OvertimeSummaryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOvertimeSummary extends EditRecord
{
    protected static string $resource = OvertimeSummaryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}

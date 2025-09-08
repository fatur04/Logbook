<?php

namespace App\Filament\Resources\OvertimeSummaryResource\Pages;

use App\Filament\Resources\OvertimeSummaryResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewOvertimeSummary extends ViewRecord
{
    protected static string $resource = OvertimeSummaryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}

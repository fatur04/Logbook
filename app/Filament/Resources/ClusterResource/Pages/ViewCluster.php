<?php

namespace App\Filament\Resources\ClusterResource\Pages;

use App\Filament\Resources\ClusterResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCluster extends ViewRecord
{
    protected static string $resource = ClusterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}

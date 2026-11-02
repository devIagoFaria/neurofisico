<?php

namespace App\Filament\Resources\SmartPatientCards\Pages;

use App\Filament\Resources\SmartPatientCards\SmartPatientCardResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSmartPatientCards extends ListRecords
{
    protected static string $resource = SmartPatientCardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

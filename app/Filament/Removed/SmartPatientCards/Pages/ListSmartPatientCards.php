<?php

namespace App\Filament\Doctor\Resources\SmartPatientCards\Pages;

use App\Filament\Doctor\Resources\SmartPatientCards\SmartPatientCardsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSmartPatientCards extends ListRecords
{
    protected static string $resource = SmartPatientCardsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

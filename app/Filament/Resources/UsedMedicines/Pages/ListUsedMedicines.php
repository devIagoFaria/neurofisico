<?php

namespace App\Filament\Resources\UsedMedicines\Pages;

use App\Filament\Resources\UsedMedicines\UsedMedicineResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUsedMedicines extends ListRecords
{
    protected static string $resource = UsedMedicineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }
}

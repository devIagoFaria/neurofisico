<?php

namespace App\Filament\Resources\UsedMedicines\Pages;

use App\Filament\Resources\UsedMedicines\UsedMedicineResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUsedMedicine extends EditRecord
{
    protected static string $resource = UsedMedicineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // DeleteAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\PurchaseMedicines\Pages;

use App\Filament\Resources\PurchaseMedicines\PurchaseMedicinesResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPurchaseMedicines extends EditRecord
{
    protected static string $resource = PurchaseMedicinesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // DeleteAction::make(),
        ];
    }
}

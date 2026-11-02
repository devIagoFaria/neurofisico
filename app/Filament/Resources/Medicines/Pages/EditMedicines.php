<?php

namespace App\Filament\Resources\Medicines\Pages;

use App\Filament\Resources\Medicines\MedicinesResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMedicines extends EditRecord
{
    protected static string $resource = MedicinesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backActionResource(static::$resource::getUrl()),
        ];
    }
    protected function getSavedNotificationTitle(): ?string
    {
        return __('messages.flash.medicine_update');
    }
}

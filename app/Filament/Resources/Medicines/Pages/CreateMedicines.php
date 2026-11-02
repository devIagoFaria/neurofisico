<?php

namespace App\Filament\Resources\Medicines\Pages;

use App\Filament\Resources\Medicines\MedicinesResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMedicines extends CreateRecord
{
    protected static string $resource = MedicinesResource::class;
    protected static bool $canCreateAnother = false;
    protected function getHeaderActions(): array
    {
        return [
            backActionResource(static::$resource::getUrl()),
        ];
    }
    protected function getCreatedNotificationTitle(): ?string
    {
        return __('messages.flash.medicine_create');
    }
}

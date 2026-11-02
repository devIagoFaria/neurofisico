<?php

namespace App\Filament\Resources\MedicineBrands\Pages;

use App\Filament\Resources\MedicineBrands\MedicineBrandResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMedicineBrand extends CreateRecord
{
    protected static string $resource = MedicineBrandResource::class;
    protected static bool $canCreateAnother = false;

    protected function getHeaderActions(): array
    {
        return [
            backActionResource(static::$resource::getUrl()),
        ];
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return __('messages.flash.medicine_brand_create');
    }
}

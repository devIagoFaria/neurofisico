<?php

namespace App\Filament\Resources\MedicineBrands\Pages;

use App\Filament\Resources\MedicineBrands\MedicineBrandResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMedicineBrand extends EditRecord
{
    protected static string $resource = MedicineBrandResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backActionResource(static::$resource::getUrl()),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return __('messages.flash.medicine_brand_update');
    }
}

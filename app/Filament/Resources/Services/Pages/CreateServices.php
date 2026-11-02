<?php

namespace App\Filament\Resources\Services\Pages;

use App\Filament\Resources\Services\ServicesResource;
use Filament\Resources\Pages\CreateRecord;

class CreateServices extends CreateRecord
{
    protected static string $resource = ServicesResource::class;
    protected static bool $canCreateAnother = false;
    protected function getHeaderActions(): array
    {
        return [
            backActionResource(static::$resource::getUrl()),
        ];
    }
    protected function getCreatedNotificationTitle(): ?string
    {
        return __('messages.flash.service_create');
    }
}

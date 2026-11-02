<?php

namespace App\Filament\Resources\Visits\Pages;

use App\Filament\Resources\Visits\VisitsResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVisits extends CreateRecord
{
    protected static string $resource = VisitsResource::class;
    protected static bool $canCreateAnother = false;
    protected function getHeaderActions(): array
    {
        return [
            backActionResource(static::$resource::getUrl()),
        ];
    }
    protected function getCreatedNotificationTitle(): ?string
    {
        return __('messages.flash.visit_create');
    }
}

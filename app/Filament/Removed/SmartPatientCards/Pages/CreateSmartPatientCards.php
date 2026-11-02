<?php

namespace App\Filament\Doctor\Resources\SmartPatientCards\Pages;

use App\Filament\Doctor\Resources\SmartPatientCards\SmartPatientCardsResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSmartPatientCards extends CreateRecord
{
    protected static string $resource = SmartPatientCardsResource::class;

    protected static bool $canCreateAnother = false;

    protected function getHeaderActions(): array
    {
        return [
            backActionResource(static::$resource::getUrl()),
        ];
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return __('messages.smart_patient_card.template_created');
    }
}

<?php

namespace App\Filament\Resources\SmartPatientCards\Pages;

use App\Filament\Resources\SmartPatientCards\SmartPatientCardResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSmartPatientCard extends CreateRecord
{
    protected static string $resource = SmartPatientCardResource::class;

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
